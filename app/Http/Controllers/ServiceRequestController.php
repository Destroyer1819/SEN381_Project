<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\Category;
use App\Models\RequestAssignment;
use App\Models\RequestComment;
use App\Models\RequestStatusHistory;
use App\Models\ServiceRequest;
use App\Services\OfferService;
use App\Services\RequestStatusService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ServiceRequestController extends Controller
{
    // The sort fields the list accepts; anything else fails validation, so no user input reaches ORDER BY
    private const SORTS = ['created_at', 'updated_at', 'reference', 'status', 'category'];

    public function create(): Response
    {

        return Inertia::render('requests/create',
            [
                'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),

            ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $serviceRequest = retry(3, fn () => DB::transaction(function () use ($data, $user) {
            $first = now()->year * 1000000;
            $last = ServiceRequest::whereBetween('reference_number', [$first, $first + 999999])
                ->max('reference_number');

            $serviceRequest = ServiceRequest::create([
                ...$data,
                'requestor_id' => $user->id,
                'reference_number' => ($last ?? $first) + 1,
            ]);

            $serviceRequest->history()->create([
                'old_status' => null,
                'new_status' => 'open',
                'changed_by' => $user->id,
            ]);

            return $serviceRequest;
        }), 0, fn ($e) => $e instanceof UniqueConstraintViolationException);

        return to_route('requests.show', $serviceRequest)
            ->with('success', 'Request submitted. Your reference is CC-'.$serviceRequest->reference_number.'.');
    }

    // index method
    public function index(Request $request): Response
    {
        $user = $request->user();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(ServiceRequest::STATUSES)],
            'category_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        // Kept out of $filters so the filter bar never sends an old sort back (FR-005, FR-013)
        $sort = $request->validate([
            'sort' => ['nullable', Rule::in(self::SORTS)],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        // Only staff and management are offered "Assigned to me"
        $mine = $user->role !== 'requestor' && $request->boolean('mine');
        // FR-021: "Active only" hides resolved and closed requests. FR-023: "Overdue only" lists the overdue ones.
        $active = $request->boolean('active');
        $overdue = $request->boolean('overdue');

        $requests = ServiceRequest::query()
            ->with(['category:id,name', 'requestor:id,name', 'assignee:id,name'])
            ->visibleTo($user)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category_id'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->when($mine, fn ($query) => $query->where('assigned_to', $user->id))
            ->when($active, fn ($query) => $query->whereNotIn('status', ['resolved', 'closed']))
            ->when($overdue, fn ($query) => $query->overdue())
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->where(function ($query) use ($q) {
                $query->whereLike('location', "%{$q}%")
                    ->orWhereLike('description', "%{$q}%");

                if (preg_match('/^(?:CC-)?(\d{1,18})$/i', trim($q), $match)) {
                    $query->orWhere('reference_number', $match[1]);
                }
            }))
            ->tap(fn (Builder $query) => $this->applySort($query, $sort['sort'] ?? null, $sort['direction'] ?? 'desc'))
            ->paginate(50)
            ->withQueryString()
            ->through(fn (ServiceRequest $r) => $this->summary($r));

        return Inertia::render('requests/index',
            [
                'requests' => $requests,
                'filters' => [...$filters, 'mine' => $mine, 'active' => $active, 'overdue' => $overdue],
                'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'statuses' => ServiceRequest::STATUSES,
            ]);
    }

    // moved to a method for through to be easily updated for later
    // Same order as the frontend's sort options: status follows the workflow, ties go to the newest id
    private function applySort(Builder $query, ?string $field, string $direction): void
    {
        $statusOrder = collect(ServiceRequest::STATUSES)
            ->map(fn (string $status, int $position) => "WHEN '{$status}' THEN {$position}")
            ->implode(' ');

        match ($field) {
            'updated_at' => $query->orderBy('updated_at', $direction),
            'reference' => $query->orderBy('reference_number', $direction),
            // $direction is validated to asc/desc above, so it is safe in raw SQL
            'status' => $query->orderByRaw("CASE status {$statusOrder} END {$direction}"),
            'category' => $query->orderBy(
                Category::select('name')->whereColumn('categories.id', 'service_requests.category_id'),
                $direction,
            ),
            default => $query->orderBy('created_at', $direction),
        };

        $query->orderByDesc('id');
    }

    private function summary(ServiceRequest $r): array
    {

        return
        [
            'id' => $r->id,
            'reference' => 'CC-'.$r->reference_number,
            'status' => $r->status,
            'category' => $r->category?->name,
            'location' => $r->location,
            'area' => $r->area,
            'description' => $r->description,
            'requestor' => $r->requestor?->name,
            'assignee' => $r->assignee?->name,
            'overdue' => $r->isOverdue(),
            'created_at' => $r->created_at->toIso8601String(),
            'updated_at' => $r->updated_at->toIso8601String(),
        ];
    }

    public function show(Request $request, ServiceRequest $serviceRequest, RequestStatusService $statuses, OfferService $offers): Response
    {
        Gate::authorize('view', $serviceRequest);

        $canOffer = Gate::allows('offer', $serviceRequest);
        $pending = $serviceRequest->pendingOffer;

        $serviceRequest->load([
            'category:id,name',
            'requestor:id,name',
            'assignee:id,name',
            'history' => fn ($query) => $query->orderBy('changed_at')->orderBy('id'),
            'history.changedBy:id,name',
            'comments' => fn ($query) => $query->orderBy('created_at')->orderBy('id'),
            'comments.author:id,name,role',
        ]);

        return Inertia::render('requests/show',
            [

                'request' => [
                    ...$this->summary($serviceRequest),
                    'version' => $serviceRequest->version,
                    'resolved_at' => $serviceRequest->resolved_at?->toIso8601String(),
                ],
                'history' => $serviceRequest->history->map(fn (RequestStatusHistory $h) => [
                    'id' => $h->id,
                    'old_status' => $h->old_status,
                    'new_status' => $h->new_status,
                    'by' => $h->changedBy?->name,
                    'at' => $h->changed_at->toIso8601String(),
                ]),
                'comments' => $serviceRequest->comments->map(fn (RequestComment $c) => [
                    'id' => $c->id,
                    'text' => $c->comment,
                    'is_resolution' => $c->is_resolution,
                    'by' => $c->author?->name,
                    'role' => $c->author?->role,
                    'at' => $c->created_at->toIso8601String(),
                ]),
                // FR-016, FR-022: every self-assignment, offer, acceptance and decline, oldest first.
                // Internal to staff and management, so requestors get an empty list.
                'assignments' => $request->user()->role === 'requestor' ? [] : $serviceRequest->assignments()
                    ->with(['assignee:id,name', 'offeredBy:id,name'])
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get()
                    ->map(fn (RequestAssignment $a) => [
                        'id' => $a->id,
                        'state' => $a->state,
                        'to' => $a->assignee?->name,
                        'by' => $a->offeredBy?->name,
                        'at' => $a->created_at->toIso8601String(),
                        'responded_at' => $a->responded_at?->toIso8601String(),
                    ]),
                'actions' => [
                    'transitions' => Gate::allows('changeStatus', $serviceRequest) ? $statuses->allowedNext($serviceRequest->status) : [],
                    'canTake' => Gate::allows('take', $serviceRequest),
                    'canOffer' => $canOffer,
                    'staff' => $canOffer
                        ? $offers->eligibleStaff($serviceRequest, $request->user())->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values()
                        : [],
                    // Only the person the offer went to sees Accept / Decline
                    'pendingOfferId' => $pending?->assigned_to === $request->user()->id ? $pending->id : null,
                    // Everyone else on the staff side sees who it is waiting on; requestors see nothing
                    'pendingOfferTo' => $pending && $pending->assigned_to !== $request->user()->id && $request->user()->role !== 'requestor'
                        ? $pending->assignee?->name
                        : null,
                ],
            ]);
    }
}

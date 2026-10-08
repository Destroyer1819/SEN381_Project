<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\Category;
use App\Models\RequestAssignment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RequestController extends Controller
{
    public function __construct(private RequestWorkflow $workflow)
    {
    }

    // FR-002 (requestor sees own) + FR-003 (staff/management search & filter)
    public function index(Request $request)
    {
        $user = $request->user();

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(config('civicconnect.statuses'))],
            'category_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'q' => ['nullable', 'string', 'max:100'],
            'mine' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['created_at', 'updated_at', 'status', 'reference_number'])],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = ServiceRequest::query()->with(['category:id,name', 'assignee:id,name', 'requestor:id,name']);

        if ($user->isRequestor()) {
            $query->where('requestor_id', $user->id); // a requestor can never see other people's requests
        }

        $query
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when(! empty($filters['mine']) && $user->isStaffOrManagement(), fn ($q) => $q->where('assigned_to', $user->id))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $term = trim($term);
                $reference = preg_replace('/^cc-/i', '', $term);
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($w) use ($like, $reference) {
                    $w->where('description', 'ilike', $like)->orWhere('location', 'ilike', $like)->orWhere('area', 'ilike', $like);
                    if (ctype_digit($reference)) {
                        $w->orWhere('reference_number', (int) $reference);
                    }
                });
            });

        $sort = $filters['sort'] ?? 'created_at';
        $dir = $filters['dir'] ?? 'desc';
        $query->orderBy($sort, $dir)->orderBy('id', 'desc');

        $paginator = $query->paginate(config('civicconnect.per_page'))->withQueryString();
        $paginator->through(fn (ServiceRequest $r) => $this->summary($r));

        return Inertia::render('requests/index', [
            'requests' => $paginator,
            'filters' => $filters,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'statuses' => config('civicconnect.statuses'),
        ]);
    }

    public function create()
    {
        return Inertia::render('requests/create', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    // FR-001
    public function store(StoreServiceRequest $request)
    {
        $created = $this->workflow->create($request->user(), $request->validated());

        return redirect()->route('requests.show', $created)->with('success', 'Request submitted. Your reference is CC-'.$created->reference_number.'.');
    }

    public function show(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();
        abort_unless($this->workflow->canView($user, $serviceRequest), 403, 'You cannot view this request.');

        $serviceRequest->load(['category:id,name', 'assignee:id,name', 'requestor:id,name']);

        $history = $serviceRequest->history()->with('changedBy:id,name')->get()->map(fn ($h) => [
            'id' => $h->id,
            'old_status' => $h->old_status,
            'new_status' => $h->new_status,
            'by' => $h->changedBy?->name,
            'at' => $h->changed_at->toIso8601String(),
        ]);

        $comments = $serviceRequest->comments()->with('author:id,name,role')->get()->map(fn ($c) => [
            'id' => $c->id,
            'text' => $c->comment,
            'is_resolution' => $c->is_resolution,
            'by' => $c->author?->name,
            'role' => $c->author?->role,
            'at' => $c->created_at->toIso8601String(),
        ]);

        $canAssign = $user->isStaffOrManagement() && $serviceRequest->status === 'open';

        $myOffer = $user->isStaff()
            ? RequestAssignment::where('request_id', $serviceRequest->id)->where('assigned_to', $user->id)->where('state', 'offered')->first()
            : null;

        return Inertia::render('requests/show', [
            'request' => $this->detail($serviceRequest),
            'history' => $history,
            'comments' => $comments,
            'actions' => [
                'transitions' => $this->workflow->availableTransitions($user, $serviceRequest),
                'canTake' => $user->isStaff() && $serviceRequest->status === 'open',
                'canOffer' => $canAssign,
                'staff' => $canAssign ? User::where('role', 'staff')->where('id', '<>', $user->id)->orderBy('name')->get(['id', 'name']) : [],
                'pendingOfferId' => $myOffer?->id,
            ],
        ]);
    }

    // FR-005
    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(config('civicconnect.statuses'))],
            'version' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->workflow->changeStatus($serviceRequest, $data['status'], $request->user(), (int) $data['version'], $data['note'] ?? null);

        return back()->with('success', 'Status updated.');
    }

    // FR-004
    public function take(Request $request, ServiceRequest $serviceRequest)
    {
        $this->workflow->selfAssign($serviceRequest, $request->user());

        return back()->with('success', 'You are now assigned to this request.');
    }

    public function offer(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate(['assigned_to' => ['required', 'integer', 'exists:users,id']]);

        $this->workflow->offer($serviceRequest, $request->user(), User::findOrFail($data['assigned_to']));

        return back()->with('success', 'Request offered.');
    }

    public function respond(Request $request, RequestAssignment $assignment)
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['accept', 'decline'])]]);

        $this->workflow->respondToOffer($assignment, $request->user(), $data['decision'] === 'accept');

        return redirect()->route('requests.show', $assignment->request_id)
            ->with('success', $data['decision'] === 'accept' ? 'Offer accepted.' : 'Offer declined.');
    }

    public function comment(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $this->workflow->addComment($serviceRequest, $request->user(), $data['body']);

        return back()->with('success', 'Comment added.');
    }

    private function summary(ServiceRequest $r): array
    {
        return [
            'id' => $r->id,
            'reference' => 'CC-'.$r->reference_number,
            'status' => $r->status,
            'category' => $r->category?->name,
            'area' => $r->area,
            'location' => $r->location,
            'description' => mb_strimwidth($r->description, 0, 120, '…'),
            'requestor' => $r->requestor?->name,
            'assignee' => $r->assignee?->name,
            'overdue' => $r->isOverdue(),
            'created_at' => $r->created_at->toIso8601String(),
            'updated_at' => $r->updated_at->toIso8601String(),
        ];
    }

    private function detail(ServiceRequest $r): array
    {
        return $this->summary($r) + [
            'description' => $r->description,
            'version' => $r->version,
            'resolved_at' => $r->resolved_at?->toIso8601String(),
        ];
    }
}

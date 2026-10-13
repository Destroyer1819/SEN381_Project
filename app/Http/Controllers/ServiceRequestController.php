<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\ServiceRequest;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\RequestComment;
use App\Models\RequestStatusHistory;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreServiceRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    private const STATUSES = ['open', 'assigned', 'in_progress', 'resolved', 'closed'];


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

        $serviceRequest = retry(3, fn () => DB::transaction(function () use ($data, $user) 
        {
            $first = now()->year * 1000000;
            $last = ServiceRequest::whereBetween('reference_number', [$first, $first + 999999])
                ->max('reference_number');

            $serviceRequest = ServiceRequest::create
            ([
                ...$data,
                'requestor_id' => $user->id,
                'reference_number' => ($last ?? $first) + 1,
            ]);

            $serviceRequest->history()->create
            ([
                'old_status' => null,
                'new_status' => 'open',
                'changed_by' => $user->id,
            ]);

            return $serviceRequest;
        }), 0, fn ($e) => $e instanceof UniqueConstraintViolationException);

        return to_route('requests.show', $serviceRequest)
            ->with('success', 'Request submitted. Your reference is CC-'.$serviceRequest->reference_number.'.');
    }
    //index method
    public function index(Request $request): Response
    {
        $user = $request->user();

        $filters = $request->validate
        ([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'category_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        // Only staff and management are offered "Assigned to me"
        $mine = $user->role !== 'requestor' && $request->boolean('mine');

        $requests = ServiceRequest::query()
            ->with(['category:id,name', 'requestor:id,name', 'assignee:id,name'])
            ->visibleTo($user)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category_id'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->when($mine, fn ($query) => $query->where('assigned_to', $user->id))
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->where(function ($query) use ($q) 
            {
                $query->whereLike('location', "%{$q}%")
                    ->orWhereLike('description', "%{$q}%");

                if (preg_match('/^(?:CC-)?(\d{1,18})$/i', trim($q), $match)) 
                {
                    $query->orWhere('reference_number', $match[1]);
                }
            }))
            ->latest()
            ->paginate(50)
            ->withQueryString()
            ->through(fn (ServiceRequest $r) => $this->summary($r));

        return Inertia::render('requests/index', 
        [
            'requests' => $requests,
            'filters' => [...$filters, 'mine' => $mine],
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'statuses' => self::STATUSES,
        ]);
    }
    //moved to a method for through to be easily updated for later
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
            'overdue' => ! in_array($r->status, ['resolved', 'closed'])
                && $r->created_at->lt(now()->subDays(7)),
            'created_at' => $r->created_at->toIso8601String(),
            'updated_at' => $r->updated_at->toIso8601String(),            
        ];
    }
    public function show(ServiceRequest $serviceRequest) : Response
    {
        Gate::authorize('view', $serviceRequest);

        $serviceRequest->load
        ([
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
            
            'request' => 
            [
                ...$this->summary($serviceRequest),
                'version' => $serviceRequest->version,
                'resolved_at' => $serviceRequest->resolved_at?->toIso8601String(),
            ],
            'history' => $serviceRequest->history->map(fn (RequestStatusHistory $h) => 
            [
                'id' => $h->id,
                'old_status' => $h->old_status,
                'new_status' => $h->new_status,
                'by' => $h->changedBy?->name,
                'at' => $h->changed_at->toIso8601String(),
            ]),
            'comments' => $serviceRequest->comments->map(fn (RequestComment $c) => 
            [
                'id' => $c->id,
                'text' => $c->comment,
                'is_resolution' => $c->is_resolution,
                'by' => $c->author?->name,
                'role' => $c->author?->role,
                'at' => $c->created_at->toIso8601String(),
            ]),
            'actions' => 
            [
                'transitions' => [],
                'canTake' => false,
                'canOffer' => false,
                'staff' => [],
                'pendingOfferId' => null,
            ],
        ]);
    }

    

}

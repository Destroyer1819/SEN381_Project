<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\ServiceRequest;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ServiceRequestController extends Controller
{
    private const STATUSES = ['open', 'assigned', 'in_progress', 'resolved', 'closed'];

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
            ->through(fn (ServiceRequest $r) => 
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
            ]);

        return Inertia::render('requests/index', 
        [
            'requests' => $requests,
            'filters' => [...$filters, 'mine' => $mine],
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'statuses' => self::STATUSES,
        ]);
    }

}

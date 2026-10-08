<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

// FR-007 - management dashboard (route is guarded by role:management)
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate([
            'category_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $base = ServiceRequest::query()
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('service_request.category_id', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('service_request.created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('service_request.created_at', '<=', $v));

        $byStatus = (clone $base)->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        $overdueCutoff = now()->subDays(config('civicconnect.overdue_days'));
        $overdue = (clone $base)->whereNotIn('status', ['resolved', 'closed'])->where('created_at', '<', $overdueCutoff)->count();

        $byCategory = (clone $base)
            ->join('categories', 'categories.id', '=', 'service_request.category_id')
            ->select('categories.name', DB::raw('count(*) as total'))
            ->groupBy('categories.name')->orderBy('categories.name')->get();

        $counts = [];
        foreach (config('civicconnect.statuses') as $status) {
            $counts[$status] = (int) ($byStatus[$status] ?? 0);
        }

        return Inertia::render('dashboard', [
            'filters' => $filters,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'stats' => [
                'total' => array_sum($counts),
                'by_status' => $counts,
                'overdue' => $overdue,
                'overdue_days' => config('civicconnect.overdue_days'),
                'by_category' => $byCategory->map(fn ($r) => ['name' => $r->name, 'total' => (int) $r->total])->values(),
            ],
        ]);
    }
}

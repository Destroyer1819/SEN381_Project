<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $filters = $request->validate([
            'category_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);
        $base = ServiceRequest::query()
            ->when($filters['category_id'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to));

        $byStatus = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $byCategory = (clone $base)->selectRaw('category_id, count(*) as total')->groupBy('category_id')->pluck('total', 'category_id');
        $names = Category::pluck('name', 'id');

        // FR-024: mean time from submission to resolution, worked out by the database so the
        // cost does not grow with the number of rows sent to the page. Same filters as every other figure.
        $secondsToResolve = DB::getDriverName() === 'pgsql'
            ? 'EXTRACT(EPOCH FROM (resolved_at - created_at))'
            : '(julianday(resolved_at) - julianday(created_at)) * 86400';
        $avgSeconds = (clone $base)->whereNotNull('resolved_at')->avg(DB::raw($secondsToResolve));

        return Inertia::render('dashboard', [
            'filters' => $filters,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'stats' => [
                'total' => $byStatus->sum(),
                'by_status' => collect(ServiceRequest::STATUSES)
                    ->mapWithKeys(fn ($status) => [$status => $byStatus[$status] ?? 0]),
                'overdue' => (clone $base)->overdue()->count(),
                'overdue_days' => ServiceRequest::OVERDUE_DAYS,
                // Null when nothing in the period has been resolved yet
                'avg_resolution_hours' => $avgSeconds === null ? null : round((float) $avgSeconds / 3600, 1),
                'by_category' => $byCategory
                    ->map(fn ($total, $id) => ['name' => $names[$id], 'total' => $total])
                    ->sortByDesc('total')
                    ->values(),
            ],
        ]);

    }
}

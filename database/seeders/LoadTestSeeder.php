<?php

namespace Database\Seeders;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Bulk data for the performance exercise:  php artisan db:seed --class=LoadTestSeeder
 * Set LOAD_ROWS (default 5000). Rows are inserted directly (no history rows) because
 * the load test only exercises list/filter reads.
 */
class LoadTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        $total = (int) env('LOAD_ROWS', 5000);
        $requestors = User::where('role', 'requestor')->pluck('id')->all();
        $staff = User::where('role', 'staff')->pluck('id')->all();
        $statuses = ['open', 'assigned', 'in_progress', 'resolved', 'closed'];
        $start = (int) (ServiceRequest::max('reference_number') ?? 1000) + 1;

        $rows = [];
        for ($i = 0; $i < $total; $i++) {
            $status = $statuses[$i % 5];
            $created = now()->subDays(random_int(0, 60))->subMinutes(random_int(0, 1000));
            $rows[] = [
                'reference_number' => $start + $i,
                'requestor_id' => $requestors[$i % count($requestors)],
                'category_id' => ($i % 5) + 1,
                'location' => 'Load test location '.($i % 50),
                'area' => 'Area '.($i % 8),
                'description' => 'Load test request number '.($i + 1).' describing a generic fault.',
                'status' => $status,
                'assigned_to' => $status === 'open' ? null : $staff[$i % count($staff)],
                'version' => 0,
                'created_at' => $created,
                'updated_at' => $created,
                'resolved_at' => in_array($status, ['resolved', 'closed'], true) ? $created->copy()->addHours(5) : null,
            ];
            if (count($rows) === 500) {
                DB::table('service_request')->insert($rows);
                $rows = [];
            }
        }
        if ($rows) {
            DB::table('service_request')->insert($rows);
        }
    }
}

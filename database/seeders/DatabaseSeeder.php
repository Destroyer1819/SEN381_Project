<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestWorkflow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo / staging data. Passwords come from SEED_PASSWORD (never hard-code real credentials).
 * Safe to run more than once.
 */
class DatabaseSeeder extends Seeder
{
    public const CATEGORIES = [
        1 => [100, 'Facility fault'],
        2 => [200, 'IT support'],
        3 => [300, 'Maintenance'],
        4 => [400, 'Lost property'],
        5 => [500, 'Security concern'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $id => [$code, $name]) {
            Category::updateOrCreate(['id' => $id], ['code' => $code, 'name' => $name, 'is_active' => true]);
        }

        $password = env('SEED_PASSWORD', 'password');

        $people = [
            ['Morgan Manager', 'management@civicconnect.test', 'management'],
            ['Sam Staff', 'staff1@civicconnect.test', 'staff'],
            ['Sasha Staff', 'staff2@civicconnect.test', 'staff'],
            ['Riley Requestor', 'requestor1@civicconnect.test', 'requestor'],
            ['Robin Requestor', 'requestor2@civicconnect.test', 'requestor'],
        ];
        foreach ($people as [$name, $email, $role]) {
            User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => $password, 'role' => $role]);
        }

        if (ServiceRequest::count() > 0) {
            return;
        }

        $workflow = app(RequestWorkflow::class);
        $r1 = User::where('email', 'requestor1@civicconnect.test')->first();
        $r2 = User::where('email', 'requestor2@civicconnect.test')->first();
        $s1 = User::where('email', 'staff1@civicconnect.test')->first();

        $samples = [
            [$r1, 1, 'Main hall', 'North wing', 'Ceiling light flickers constantly in the main hall.'],
            [$r1, 2, 'Room 204', 'Admin block', 'Projector will not connect to any laptop.'],
            [$r2, 3, 'Car park B', 'East', 'Pothole near the entrance gate is getting larger.'],
            [$r2, 5, 'Back gate', 'West', 'Gate lock appears broken and does not latch.'],
            [$r1, 4, 'Reception', 'Admin block', 'Found a black umbrella near the reception desk.'],
        ];
        $created = [];
        foreach ($samples as [$who, $cat, $location, $area, $desc]) {
            $created[] = $workflow->create($who, ['category_id' => $cat, 'location' => $location, 'area' => $area, 'description' => $desc]);
        }

        // Move a couple through the workflow so every status is represented in the demo.
        $workflow->selfAssign($created[1], $s1);
        $workflow->selfAssign($created[2], $s1);
        $fresh = $created[2]->fresh();
        $workflow->changeStatus($fresh, 'in_progress', $s1, $fresh->version);

        // Backdate one open request so the "overdue" indicator is visible in the demo.
        DB::table('service_request')->where('id', $created[0]->id)->update([
            'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10),
        ]);
        DB::table('request_status_history')->where('request_id', $created[0]->id)->update(['changed_at' => now()->subDays(10)]);
    }
}

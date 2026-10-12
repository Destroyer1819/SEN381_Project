<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with TEST data.
     *
     * Run on a fresh database with:  php artisan migrate:fresh --seed
     * All people and emails are fictional (@example.com). Every password is: password
     */
    public function run(): void
    {
        // Test data must never end up in the live system.
        if (app()->environment('production')) {
            $this->command->error('Refusing to seed test data in production.');

            return;
        }

        $this->call([
            CategorySeeder::class,  // the 7 categories from D-001
            ScenarioSeeder::class,  // 8 named users + 10 hand-made scenarios (R1 to R10)
            VolumeSeeder::class,    // thousands of generated requests for performance tests (NFR-004)
        ]);

        // Rows above were inserted with fixed IDs, so move the PostgreSQL ID counters
        // past them. Otherwise the app's first new row would clash with an existing ID.
        if (DB::getDriverName() === 'pgsql') {
            foreach (['users', 'categories', 'service_requests'] as $table) {
                DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), (SELECT MAX(id) FROM {$table}))");
            }
        }
    }
}

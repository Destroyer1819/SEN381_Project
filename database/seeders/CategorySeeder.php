<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * The controlled category list from D-001, including the 'Other' catch-all.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['id' => 1, 'code' => 101, 'name' => 'Facility faults',   'is_active' => true],
            ['id' => 2, 'code' => 102, 'name' => 'IT support',        'is_active' => true],
            ['id' => 3, 'code' => 103, 'name' => 'Maintenance',       'is_active' => true],
            ['id' => 4, 'code' => 104, 'name' => 'Lost property',     'is_active' => true],
            ['id' => 5, 'code' => 105, 'name' => 'Security concerns', 'is_active' => true],
            ['id' => 6, 'code' => 106, 'name' => 'Damaged equipment', 'is_active' => true],
            ['id' => 7, 'code' => 107, 'name' => 'Other',             'is_active' => true],
        ]);
    }
}

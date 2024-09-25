<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class DashboardSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('dashboard_settings')->insert([
            ['permission_id' => 1, 'user_id' => 1, 'company_id' => 1, 'is_primary' =>0],
            ['permission_id' => 2, 'user_id' => 1, 'company_id' => 1, 'is_primary' =>1],
//                ['permission_id' => 3, 'user_id' => 1, 'company_id' => 1, 'is_primary' =>0],
            ['permission_id' => 4, 'user_id' => 1, 'company_id' => 1, 'is_primary' =>1],
            ['permission_id' => 5, 'user_id' => 1, 'company_id' => 1, 'is_primary' =>0],
            ['permission_id' => 6, 'user_id' => 1, 'company_id' => 1, 'is_primary' =>1],
        ]);
    }
}

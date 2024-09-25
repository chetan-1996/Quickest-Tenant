<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class LostReasonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lost_reasons')->insert([
            ['name' => 'Costly', 'user_id' => 1, 'company_id' => 1, "priority" => 0],
                ['name' => 'Duplicate Lead', 'user_id' => 1, 'company_id' => 1, "priority" => 0],
                ['name' => 'Finalize other solution', 'user_id' => 1, 'company_id' => 1, "priority" => 0],
                ['name' => 'No budget', 'user_id' => 1, 'company_id' => 1, "priority" => 0],
                ['name' => 'No Need', 'user_id' => 1, 'company_id' => 1, "priority" => 0],
                ['name' => 'Only Info. required', 'user_id' => 1, 'company_id' => 1, "priority" => 0],
                ['name' => 'Require specific brand only', 'user_id' => 1, 'company_id' => 1, "priority" => 0],
                ['name' => 'Others', 'user_id' => 1, 'company_id' => 1, "priority" => 1]
        ]);
    }
}

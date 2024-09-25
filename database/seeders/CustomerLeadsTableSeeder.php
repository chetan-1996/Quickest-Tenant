<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class CustomerLeadsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('customer_leads')->insert([
            ['name' => 'Social Media', 'user_id' => 1, 'company_id' => 1],
            ['name' => 'Reference', 'user_id' => 1, 'company_id' => 1],
            ['name' => 'Physical Marketing', 'user_id' => 1, 'company_id' => 1]
        ]);
    }
}

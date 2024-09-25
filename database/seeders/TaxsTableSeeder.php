<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class TaxsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('taxs')->insert([
            ['name' => 0.00, 'user_id' => 1, 'company_id' => 1],
            ['name' => 5.00, 'user_id' => 1, 'company_id' => 1],
            ['name' => 12.00, 'user_id' => 1, 'company_id' => 1],
            ['name' => 13.8, 'user_id' => 1, 'company_id' => 1],
            ['name' => 18.00, 'user_id' => 1, 'company_id' => 1],
            ['name' => 28.00, 'user_id' => 1, 'company_id' => 1]
        ]);
    }
}

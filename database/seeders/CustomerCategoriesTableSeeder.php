<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class CustomerCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('customer_categories')->insert([
            ['name' => 'B2B', 'user_id' => 1, 'company_id' => 1],
            ['name' => 'B2C', 'user_id' => 1, 'company_id' => 1]
        ]);
    }
}

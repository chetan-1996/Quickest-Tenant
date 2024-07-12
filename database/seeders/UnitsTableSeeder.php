<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UnitsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        dd(tenant());
        DB::table('units')->insert([
            ['name' => 'Site', 'user_id' => 1, 'company_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Kw', 'user_id' => 1, 'company_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Nos', 'user_id' => 1, 'company_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Kg', 'user_id' => 1, 'company_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Meter', 'user_id' => 1, 'company_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            // Add more units as needed
        ]);
    }
}

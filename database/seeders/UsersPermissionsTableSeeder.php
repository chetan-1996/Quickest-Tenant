<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class UsersPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users_permissions')->insert([
            ['permission_id' => 78, 'user_id' => 1, 'company_id' => 1],
            ['permission_id' => 77, 'user_id' => 1, 'company_id' => 1],
            ['permission_id' => 76, 'user_id' => 1, 'company_id' => 1],
            ['permission_id' => 75, 'user_id' => 1, 'company_id' => 1],
            ['permission_id' => 74, 'user_id' => 1, 'company_id' => 1],
            ['permission_id' => 73, 'user_id' => 1, 'company_id' => 1],
            ['permission_id' => 70, 'user_id' => 1, 'company_id' => 1]
        ]);
    }
}

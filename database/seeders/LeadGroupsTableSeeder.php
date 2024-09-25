<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class LeadGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userDatas = DB::table('users')->where('id', '=', 1)->select(['company_category', 'name', 'email'])->first();
        if ($userDatas->company_category == 1) {
            DB::table('lead_groups')->insert([
                ['name' => 'Cold Lead', 'color_code' => '#006398', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Dispatch Pending', 'color_code' => '#fa4e64', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Doc Query', 'color_code' => '#fdac64', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Hot Lead', 'color_code' => '#ab408b', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Installation Pending', 'color_code' => '#fa4e64', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Loan Pending', 'color_code' => '#fa4e64', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Meter Pending', 'color_code' => '#fa4e64', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Owner Ref.', 'color_code' => '#43516c', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Payment pending', 'color_code' => '#fa4e64', 'user_id' => 1, 'company_id' => 1],
            ]);
        }

        if ($userDatas->company_category != 1) {
            DB::table('lead_groups')->insert([
                ['name' => 'Hot Lead', 'color_code' => '#fa4e64', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Cold Lead', 'color_code' => '#13a764', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Close won', 'color_code' => '#3d7a44', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Agreed to Buy', 'color_code' => '#ab408b', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Need Support', 'color_code' => '#006398', 'user_id' => 1, 'company_id' => 1],
                ['name' => 'Qualified for Future', 'color_code' => '#ab4040', 'user_id' => 1, 'company_id' => 1],
            ]);
        }
    }
}

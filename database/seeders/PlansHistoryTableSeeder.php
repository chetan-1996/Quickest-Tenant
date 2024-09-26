<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class PlansHistoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $start_date = date('Y-m-d H:i:s');
        $from_date = date('Y-m-d H:i:s', strtotime("+7 day", strtotime($start_date)));

        $user_id = tenant('id');
        
        $plan = DB::table('plans')->where('isDefault', 1)->first();

        DB::table('users')->where('id', $user_id)->update([
            'plan_id' => $plan->id
        ]);

        DB::table('plan_history')->insert([
            ['user_id' => '1','plan_id' => $plan->id, 
            'user_limit' => $plan->users_limit,
            'estimate_limit' => $plan->estimate_limit, 
            'status' => 1,
            'start_date' => $start_date,
            'end_date' => $from_date ],
        ]);
    }
}

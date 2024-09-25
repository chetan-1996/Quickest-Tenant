<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class LeadStageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lead_stages')->insert([
            ['name' => 'New Lead', 'color_code' => '#006398','is_default'=>1,'priority'=>1,'is_delete'=>1, 'user_id' => 1, 'company_id' => 1],
            ['name' => 'Inprocess', 'color_code' => '#fdac64','is_default'=>0,'priority'=>2,'is_delete'=>0, 'user_id' => 1, 'company_id' => 1],
            ['name' => 'Qualified', 'color_code' => '#c47933','is_default'=>0,'priority'=>3,'is_delete'=>0, 'user_id' => 1, 'company_id' => 1],
            ['name' => 'Estimate Sent', 'color_code' => '#f678c3','is_default'=>4,'priority'=>6,'is_delete'=>1, 'user_id' => 1, 'company_id' => 1],
            ['name' => 'Lead Won', 'color_code' => '#13a764','is_default'=>2,'priority'=>10,'is_delete'=>1, 'user_id' => 1, 'company_id' => 1],
            ['name' => 'Lead Lost', 'color_code' => '#fa4e64','is_default'=>6,'priority'=>8,'is_delete'=>1, 'user_id' => 1, 'company_id' => 1],
            ['name' => 'On Hold', 'color_code' => '#ab408b','is_default'=>0,'priority'=>9,'is_delete'=>0, 'user_id' => 1, 'company_id' => 1],
            ['name' => 'Site visit schedule', 'color_code' => '#006398','is_default'=>0,'priority'=>4,'is_delete'=>0, 'user_id' => 1, 'company_id' => 1],
            ['name' => 'Site visit done', 'color_code' => '#fdac64','is_default'=>0,'priority'=>5,'is_delete'=>0, 'user_id' => 1, 'company_id' => 1],
            ['name' => 'Negotiation', 'color_code' => '#c47933','is_default'=>0,'priority'=>7,'is_delete'=>0, 'user_id' => 1, 'company_id' => 1],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('plans')->insert([
            ['id' => '1','user_id' => '1','name' => 'Free Plan','description' => 'This is a free plan','price' => '299','users_limit' => '2','estimate_limit' => '2','yearly_price' => '0','status' => '1','isDefault' => '0'],
            ['id' => '2','user_id' => '1','name' => 'Pro Plan','description' => 'Pro Plan','price' => '0','users_limit' => '2','estimate_limit' => NULL,'yearly_price' => '9999','status' => '0','isDefault' => '1'],
            ['id' => '8','user_id' => '1','name' => 'Design','description' => 'Design','price' => '1000','users_limit' => '2','estimate_limit' => NULL,'yearly_price' => '9000','status' => '0','isDefault' => '0'],
            ['id' => '9','user_id' => '1','name' => 'Profile set up & Design','description' => NULL,'price' => '10000','users_limit' => '0','estimate_limit' => '0','yearly_price' => '0','status' => '0','isDefault' => '0'],
            ['id' => '10','user_id' => '1','name' => 'Test User Ultra Pro Plan','description' => 'testing for more than 20 users for application performance','price' => '500','users_limit' => '25','estimate_limit' => NULL,'yearly_price' => NULL,'status' => '0','isDefault' => '0']
        ]);
    }
}

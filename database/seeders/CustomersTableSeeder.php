<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use App\Helpers\LogActivity;

class CustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country_id = tenant('country_id'); 
        // $country_id = 101; 
        $lead_stage_data = DB::table('lead_stages')->where('company_id', 1)->where('is_default',1)->select('name','id')->first();

        $country_data = DB::table('countries')->where('id', $country_id)->select('name', 'phonecode', 'currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->first();
        
        $customersArr = [
            ['customer_type' => 'Individual', 'name' => 
            'Quickest Support', 'phone_no' => '9724294153', 
            'email' => 'contact@quickestimate.co', 
            'country_code' => '+'.$country_data->phonecode, 
            'user_id' => 1, 
            'company_id' => 1, 
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 
            'assigned_to_user' => 1, 
            'country_id' => $country_id, 
            'state_id' => 0, 'city_name' => 'Surat', 
            'address' => '97 Dadiseth Agiary Lane Kalbadevi',
            'currency_name' => $country_data->currency_code,
            'whatsapp_country_code' => '+'.$country_data->phonecode,
            "whatsapp_no" =>'9724294153',
            'phone_no_country_id'=>$country_id,
            'whatsapp_no_country_id'=>$country_id,
            'currency_name_country_id'=>$country_id,
            'lead_stage_id' =>$lead_stage_data->id]
        ];
        DB::table('customers')->insert($customersArr);
        $customersInsertId = DB::getPdo()->lastInsertId();
        if($customersInsertId){
            $logInput['internal_remarks'] = "Lead added by manually";
            // $logInput['new_lead_flag'] = 1;
            $logInput['activity_type'] = 6;
            $logInput['assigned_to'] = 1;
            $logInput['customer_id'] = $customersInsertId;
            $logInput['entry_type'] = "leads";

            $logInput['user_id'] = 1;
            $logInput['company_id'] = 1;
            $logInput['created_by'] = 1;
            $logInput['updated_by'] = 1;
            LogActivity::addToActivityLogTenant($logInput);

            $logInput['activity_type'] = 8;
            $logInput['entry_type'] = "assigned";
            $logInput['follow_up_datetime'] = Carbon::now();
            $logInput['internal_remarks'] = "Assigned to " . tenant('name');
            LogActivity::addToActivityLogTenant($logInput);
        }

    }
}

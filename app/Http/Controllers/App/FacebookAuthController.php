<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Helpers\LogActivity;

class FacebookAuthController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }
    
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->scopes(['email', 'pages_read_engagement', 'pages_manage_metadata', 'pages_show_list', 'ads_management', 'leads_retrieval','pages_manage_ads','ads_read','business_management'])->redirect();
    }

    public function handleProviderCallback()
    {

        $user = Socialite::driver('facebook')->user();

        $quickest_user = \Illuminate\Support\Facades\Auth::user();
        $quickest_company_id = ($quickest_user->company_id) ? $quickest_user->company_id : $quickest_user->id;
        User::where('id', $quickest_company_id)->update(["facebook_email" => $user->email, "facebook_id" => $user->id, "facebook_token" => $user->token,"facebook_name" => $user->name]);

        if(!$quickest_user->facebook_id) {
            $data['name'] = 'Test Lead';
            $data['phone_no'] = '9724294153';
            $data['whatsapp_no'] = '9724294153';
            $data['country_code'] = '+91';
            $data['whatsapp_country_code'] = '+91';
            $data['currency_name'] = 'INR';
            $data['email'] = 'contact@quickestimate.co';
            $data['phone_no_country_id'] = 101;
            $data['whatsapp_no_country_id'] = 101;
            $data['currency_name_country_id'] = 101;
            $data['new_lead_flag'] = 1;
            $data['user_id'] = $quickest_user->id;
            $data['company_id'] = ($quickest_user->company_id) ? $quickest_user->company_id : $quickest_user->id;
            $data['description'] = "Facebook Lead via Test Page
            Campaign: Test Campaign

            Full Name: Test Lead
            Phone Number: +91 9724294153
            Email: contact@quickestimate.co

            This is a test client created by the
            Facebook Lead Ads integration.";

            $customer = Customer::create($data);
            $ids = $customer->id;

            $logInput['assigned_to'] = $quickest_user->id;
            $logInput['activity_type'] = 6;
            $logInput['internal_remarks'] = "Lead added from Facebook";
            $logInput['customer_id'] = $ids;
            $logInput['entry_type'] = "leads";

            $logInput['user_id'] = $quickest_user->id;
            $logInput['company_id'] = ($quickest_user->company_id) ? $quickest_user->company_id : $quickest_user->id;
            $logInput['created_by'] = $quickest_user->id;
            $logInput['updated_by'] = $quickest_user->id;
            LogActivity::addToActivityLog($logInput);

        }

        return redirect('/integration/facebook-leads-routing?flag=1');
        // Use $user to authenticate and store user data in your app.
    }
}

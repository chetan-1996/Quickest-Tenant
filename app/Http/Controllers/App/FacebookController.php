<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Customer;
use App\Models\CustomerLead;
use App\Models\EstimateTimeline;
use App\Models\LeadAssignFbUser;
use App\Models\LeadStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use libphonenumber\PhoneNumberUtil;
use LogActivity;

class FacebookController extends Controller
{

    /**
     * Login Using Facebook
     */
    public function loginUsingFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleWebhook(Request $request)
    {
        $verifyToken = 'your_verification_token';

        // Log the incoming request for debugging purposes
        Log::channel('webhook')->info('Incoming webhook request', ['data' => $request->all()]);

        if ($request->method() === 'GET' && $request->has('hub_mode') && $request->has('hub_verify_token')) {
            $mode = $request->input('hub_mode');
            $token = $request->input('hub_verify_token');
//            Log::channel('webhook')->info('Facebook', ['data' => $request]);
            if ($mode === 'subscribe' && $token === $verifyToken) {
                return response($request->input('hub_challenge'), 200)
                    ->header('Content-Type', 'text/plain');
            } else {
                return response('Invalid Verify Token', 403);
            }
        }

        $input = $request->getContent();
        $data = json_decode($input, true);

// Log the decoded data for debugging purposes
        Log::channel('webhook')->info('Decoded webhook data', ['data' => $data]);

        if (!empty($data) && isset($data['entry']) && !is_null($data['entry'])) {
            // Process the events, e.g., handle leadgen events
            $i = 0;
            foreach ($data['entry'] as $entry) {
                $i++;
                $page_id = $entry['id'];
                $facebookPageInfos = \DB::table('facebook_pages')->where('page_id', '=', $page_id)->select("page_id", "user_id", "company_id")->get();
                if ($facebookPageInfos->count() > 0) {
                    foreach ($facebookPageInfos as $facebookPageInfo) {
                        Log::channel('webhook')->info('facebookPageInfo webhook data', ['data' => $facebookPageInfo]);
                        $user1 = \DB::table('users')->where('id', '=', $facebookPageInfo->company_id)->select("*")->first();
                        $accessToken = $user1->facebook_token;
                        foreach ($entry['changes'] as $change) {
                            Log::channel('webhook')->info('change webhook data', ['data' => $change]);
                            if ($change['field'] === 'leadgen') {
                                $leadData = $change['value'];
                                $formId = $leadData['form_id'];
                                $formName = '';
                                $formUrl = "https://graph.facebook.com/v20.0/$formId?fields=name&access_token=$accessToken";

                                $formResponse = Http::get($formUrl);

                                if ($formResponse->successful()) {
                                    $formData = $formResponse->json();
                                    if (isset($formData['name'])) {
                                        $formName = $formData['name'];
                                    }
                                }

                                $leadgenId = $leadData['leadgen_id'];
                                $pageId = $leadData['page_id'];
                                $pageName = '';
                                $pageUrl = "https://graph.facebook.com/v20.0/$pageId?fields=name&access_token=$accessToken";
                                $pageResponse = Http::get($pageUrl);

                                if ($pageResponse->successful()) {
                                    $pageData = $pageResponse->json();
                                    if (isset($pageData['name'])) {
                                        $pageName = $pageData['name'];
                                    }
                                }

                                $user = DB::table('users')->where('id', '=', $facebookPageInfo->company_id)->select("*")->first();
                                $access_token = $user->facebook_token;
                                $company_id = ($user->company_id) ? $user->company_id : $user->id;
                                $logged_user_company = User::select(["id", "company_id", "lead_merge_flag"])->where('id', $company_id)->first();
                                $userId = $user->id;
                                if (Customer::where('leadgen_id', '=', $leadgenId)->where('company_id', $company_id)->count() == 0) {

                                    // Facebook
                                    $lead_id = CustomerLead::select('id')->where(function ($query) use ($company_id) {
                                        $query->where('name', 'Facebook');

                                        $query->where('company_id', $company_id);
                                        $query->where('status', '=', 0);
                                    })->first();

                                    if (empty($lead_id)) {
                                        $category_array = [
                                            'name' => 'Facebook',
                                            'user_id' => $company_id,
                                            'company_id' => $company_id
                                        ];
                                        $customerLeadId = CustomerLead::create($category_array);
                                        $customer_lead_id = $customerLeadId['id'];
                                    } else {
                                        $customer_lead_id = $lead_id['id'];
                                    }


                                    $response = Http::get("https://graph.facebook.com/v20.0/$formId", [
//                            'fields' => 'field_data,ad_name,campaign_name',
                                        'fields' => 'leads',
                                        'access_token' => $access_token,
                                    ]);

                                    $dataObjs = $response->json();
                                    if (array_key_exists('leads', $dataObjs)) {
                                        DB::enableQueryLog();
                                        $lastAssignedUser = LeadAssignFbUser::join("users", 'lead_assign_fb_users.user_id', '=', 'users.id')->where('lead_assign_fb_users.company_id', $company_id)->where('lead_assign_fb_users.assigned_list', 0)->select('lead_assign_fb_users.user_id', 'users.name', 'lead_assign_fb_users.company_id', 'lead_assign_fb_users.id', 'lead_assign_fb_users.assigned_list', 'users.device_key', 'users.mobile_device_key')->orderBy('lead_assign_fb_users.assigned_list', 'ASC')->orderBy('lead_assign_fb_users.id', 'ASC')->first();

                                        Log::channel('webhook')->info('query_1', ["query_1" => DB::getQueryLog($lastAssignedUser)]);
                                        if (!$lastAssignedUser) {
                                            DB::enableQueryLog();
                                            LeadAssignFbUser::where('company_id', $company_id)->update(['assigned_list' => 0]);
                                            $lastAssignedUser = LeadAssignFbUser::join("users", 'lead_assign_fb_users.user_id', '=', 'users.id')->where('lead_assign_fb_users.company_id', $company_id)->where('lead_assign_fb_users.assigned_list', 0)->select('lead_assign_fb_users.user_id', 'users.name', 'lead_assign_fb_users.company_id', 'lead_assign_fb_users.id', 'lead_assign_fb_users.assigned_list', 'users.device_key', 'users.mobile_device_key')->orderBy('lead_assign_fb_users.assigned_list', 'ASC')->orderBy('lead_assign_fb_users.id', 'ASC')->first();
                                            Log::channel('webhook')->info('query_2', ["query_2" => DB::getQueryLog($lastAssignedUser)]);
                                        }
//                        if ($dataObjs['leads']) {
                                        $output = [];
                                        foreach ($dataObjs['leads']['data'][0]['field_data'] as $field) {
                                            $fieldName = $field['name'];
                                            $fieldValue = $field['values'][0];
                                            $output[$fieldName] = $fieldValue;
                                        }
                                        Log::channel('webhook')->info('Test_' . $userId . '_' . $company_id, ['data' => $dataObjs['leads']['data'][0]['field_data']]);
                                        // Print the output in the desired format
                                        $insertArr = [];
                                        $description = '';
                                        foreach ($output as $fieldName => $fieldValue) {
                                            if ($fieldName == 'full_name' || $fieldName == 'full name' || $fieldName == 'name')
                                                $insertArr['name'] = $fieldValue;
                                            if ($fieldName == 'phone_number') {
                                                $mobileNumber = $fieldValue; // Replace this with the mobile number you want to parse
                                                $insertArr['phone_no'] = $fieldValue;
                                                //$pattern = '/^(\+?\d{1,4})?[\s-]?\(?\d{1,4}\)?[\s-]?\d{3,4}[\s-]?\d{3,4}$/';
                                                $pattern = '/^\+\d{1,4}[\s-]?\(?\d{1,4}\)?[\s-]?\d{3,4}[\s-]?\d{3,4}$/';
                                                if (preg_match($pattern, $fieldValue)) {
                                                    $countryInfo = $this->getCountryInfoFromMobileNumber($fieldValue);
                                                } else {
                                                    $countryInfo = [];

                                                    Log::channel('webhook')->info('Invalid_mobile_format_' . $userId . '_' . $company_id, ['data' => $fieldValue]);
                                                }
                                                //$countryInfo = $this->getCountryInfoFromMobileNumber($fieldValue);
                                                //Log::channel('webhook')->info('Mobile_no_' . $userId . '_' . $company_id, ['data' => $countryInfo, "testing mobile no" > $fieldValue]);
                                                if (!empty($countryInfo)) {
                                                    $insertArr['country_code'] = "+{$countryInfo['country_code']}";
                                                    $insertArr['whatsapp_country_code'] = "+{$countryInfo['country_code']}";
                                                    $country = Country::where('sortname', $countryInfo['country_short_name'])->first();
                                                    if ($country) {
                                                        $insertArr['country_id'] = $country->id;
                                                        $insertArr['currency_name'] = $country->currency_code;
                                                        $insertArr['phone_no_country_id'] = $country->id;
                                                        $insertArr['whatsapp_no_country_id'] = $country->id;
                                                        $insertArr['currency_name_country_id'] = $country->id;
                                                    }

                                                    $phoneNumberWithoutCountryCode = substr($mobileNumber, strlen("+{$country->phonecode}"));
                                                    $insertArr['phone_no'] = $phoneNumberWithoutCountryCode;
                                                    $insertArr['whatsapp_no'] = $phoneNumberWithoutCountryCode;
                                                }


                                            }
                                            if (!($fieldName == 'full_name' || $fieldName == 'phone_number'))
                                                $description .= "$fieldName: $fieldValue\n";
                                        }
                                        $description .= "Form Name: $formName\n";
                                        $description .= "Page Name: $pageName\n";
                                        $description .= "Leadgen Id: $leadgenId\n";

                                        if (!array_key_exists('country_code', $insertArr))
                                            $insertArr['country_code'] = '+91';
                                        if (!array_key_exists('whatsapp_country_code', $insertArr))
                                            $insertArr['whatsapp_country_code'] = '+91';
                                        $insertArr['description'] = $description;

                                        if (!array_key_exists('currency_name', $insertArr))
                                            $insertArr['currency_name'] = 'INR';
                                        if (!array_key_exists('country_id', $insertArr))
                                            $insertArr['country_id'] = 101;
                                        if (!array_key_exists('phone_no_country_id', $insertArr))
                                            $insertArr['phone_no_country_id'] = 101;
                                        if (!array_key_exists('whatsapp_no_country_id', $insertArr))
                                            $insertArr['whatsapp_no_country_id'] = 101;
                                        if (!array_key_exists('currency_name_country_id', $insertArr))
                                            $insertArr['currency_name_country_id'] = 101;
                                        $insertArr['new_lead_flag'] = 1;
                                        $insertArr['customer_lead_id'] = $customer_lead_id;
                                        $insertArr['user_id'] = $userId;
                                        $insertArr['company_id'] = $company_id;

                                        $assigned_to_user = $lastAssignedUser->user_id;
                                        $assigned_to_user_name = $lastAssignedUser->name;
                                        $device_key = $lastAssignedUser->device_key;
                                        $mobile_device_key = $lastAssignedUser->mobile_device_key;
                                        $existData = Customer::where('phone_no', '=', $insertArr['phone_no'])
                                            ->where('company_id', $company_id)
                                            ->orderBy('id', 'DESC')
                                            ->first();
                                        if ($existData) {
                                            $assigned_to_user = $existData->assigned_to_user;
                                            DB::enableQueryLog();
                                            $exist_user1 = User::select(["id", "name", "device_key", "mobile_device_key"])->where('id', $assigned_to_user)->first(); //->where('company_id', $company_id)

//                                            Log::channel('webhook')->info('Facebook', ['data' => DB::getQueryLog($exist_user1)]);
                                            $assigned_to_user_name = $exist_user1->name;
                                            $device_key = $exist_user1->device_key;
                                            $mobile_device_key = $exist_user1->mobile_device_key;
                                        }

                                        $insertArr['assigned_to_user'] = $user1->facebook_integration == "Unassigned" ? '' : $assigned_to_user;
                                        $insertArr['leadgen_id'] = $leadgenId;

                                        $insertArr['lead_stage_id'] = 0;
                                        $lead_stage_data = LeadStage::where('company_id', $company_id)->where('is_default', 1)->select('name', 'id')->first();
                                        if ($lead_stage_data) {
                                            $insertArr['lead_stage_id'] = $lead_stage_data->id;
                                        }
                                        $existData = '';
                                        if (array_key_exists('phone_no', $insertArr)) {
                                            $existData = Customer::where('phone_no', '=', $insertArr['phone_no'])
                                                ->where('company_id', $company_id)
                                                ->orderBy('id', 'DESC')
                                                ->first();
                                        }

                                        if (!$existData || $logged_user_company->lead_merge_flag == 0) {
                                            $customer = Customer::create($insertArr);
                                            $ids = $customer->id;

                                            $logInput['assigned_to'] = $company_id;
                                            $logInput['activity_type'] = 6;
                                            $logInput['internal_remarks'] = "Lead added from Facebook";
                                            $logInput['customer_id'] = $ids;
                                            $logInput['entry_type'] = "leads";

                                            $logInput['user_id'] = $userId;
                                            $logInput['company_id'] = $company_id;
                                            $logInput['created_by'] = $company_id;
                                            $logInput['updated_by'] = $company_id;
                                            LogActivity::addToActivityLogGuest($logInput);

                                            if ($user1->facebook_integration != "Unassigned") {
                                                $logInput['activity_type'] = 8;
                                                $logInput['entry_type'] = "assigned";
                                                $logInput['internal_remarks'] = "Assigned to " . $lastAssignedUser->name;
                                                LogActivity::addToActivityLogGuest($logInput);

                                                $noficationArr['customer_id'] = $ids;
                                                $noficationArr['notification_type'] = "assign_to_you";
                                                $noficationArr['device_key'] = $device_key;
                                                $noficationArr['mobile_device_key'] = $mobile_device_key;
                                                $noficationArr['title'] = 'New Lead Assigned To You';
                                                $noficationArr['body'] = $insertArr['name'] . ' is assigned to you by ' . $lastAssignedUser->name;
                                                $this->sendAssigntoUserNotification($noficationArr);
                                            }
                                            Log::channel('webhook')->info('Before Lead Merge' . $userId . '_' . $company_id, ['data' => $logInput, "testing mobile no" > $fieldValue]);

                                        }


                                        if ($existData && $logged_user_company->lead_merge_flag == 1) {

                                            $existDatafollowup = EstimateTimeline::where('customer_id', '=', $existData->id)
                                                ->where('company_id', $company_id)
                                                ->select('follow_up_datetime')
                                                ->orderBy('id', 'DESC')
                                                ->first();
                                            $logInput['follow_up_datetime'] = $existDatafollowup->follow_up_datetime;
                                            $logInput['assigned_to'] = $assigned_to_user;
                                            $logInput['activity_type'] = 20;
                                            $logInput['entry_type'] = "merge";
                                            $logInput['activity_name'] = "Lead Merge";
                                            $logInput['internal_remarks'] = $description;
                                            $logInput['activity_notes'] = $description;
                                            $logInput['customer_id'] = $existData->id;
                                            $logInput['user_id'] = $company_id;
                                            $logInput['company_id'] = $company_id;
                                            $logInput['created_by'] = $company_id;
                                            $logInput['updated_by'] = $company_id;
                                            LogActivity::addToActivityLogGuest($logInput);
                                            $leadstagedata = LeadStage::select(["name", "id"])
                                                ->where('name', '=', 'New Lead')
                                                ->where('status', '=', 0)
                                                ->where('company_id', $company_id)
                                                ->orderBy('id','DESC')
                                                ->first();
                                            if($leadstagedata){
                                                Customer::where('id', $existData->id) // Replace with your condition
                                                ->update(['lead_stage_id' => $leadstagedata->id]);
                                            }
                                            Log::channel('webhook')->info('Before Lead Merge Last' . $userId . '_' . $company_id, ['data' => $logInput, "testing mobile no" > $fieldValue]);
                                            $noficationArr['customer_id'] = $existData->id;
                                            $noficationArr['notification_type'] = "assign_to_you";
                                            $noficationArr['device_key'] = $device_key;
                                            $noficationArr['mobile_device_key'] = $mobile_device_key;
                                            //$noficationArr['mobile_device_key'] = null;
                                            $noficationArr['title'] = 'Lead Merge';
                                            $noficationArr['body'] = $insertArr['name'] . ' is assigned to you by ' . $assigned_to_user_name;
                                            $this->sendAssigntoUserNotification($noficationArr);
                                        }

                                        /*$customer = Customer::create($insertArr);
                                        $ids = $customer->id;

                                        $logInput['assigned_to'] = $company_id;
                                        $logInput['activity_type'] = 6;
                                        $logInput['internal_remarks'] = "Lead added from Facebook";
                                        $logInput['customer_id'] = $ids;
                                        $logInput['entry_type'] = "leads";

                                        $logInput['user_id'] = $userId;
                                        $logInput['company_id'] = $company_id;
                                        $logInput['created_by'] = $company_id;
                                        $logInput['updated_by'] = $company_id;
                                        LogActivity::addToActivityLogGuest($logInput);

                                        if ($user1->facebook_integration != "Unassigned") {
                                            $logInput['activity_type'] = 8;
                                            $logInput['entry_type'] = "assigned";
                                            $logInput['internal_remarks'] = "Assigned to " . $lastAssignedUser->name;
                                            LogActivity::addToActivityLogGuest($logInput);

                                            $noficationArr['customer_id'] = $ids;
                                            $noficationArr['notification_type'] = "assign_to_you";
                                            $noficationArr['device_key'] = $lastAssignedUser->device_key;
                                            $noficationArr['mobile_device_key'] = $lastAssignedUser->mobile_device_key;
                                            $noficationArr['title'] = 'New Lead Assigned To You';
                                            $noficationArr['body'] = $insertArr['name'] . ' is assigned to you by ' . $lastAssignedUser->name;
                                            $this->sendAssigntoUserNotification($noficationArr);
                                        }*/

                                        LeadAssignFbUser::where('company_id', $company_id)->where("user_id", $assigned_to_user)->update(['assigned_list' => 1]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return response("Facebook Received data successfully", 200);
    }

    public function getCountryInfoFromMobileNumber($phoneNumber)
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        try {
            $parsedNumber = $phoneNumberUtil->parse($phoneNumber, null);

            // Get the country code
            $countryCode = $phoneNumberUtil->getCountryCodeForRegion(
                $phoneNumberUtil->getRegionCodeForNumber($parsedNumber)
            );

            // Get the country short name (ISO code)
            $countryShortName = strtoupper($phoneNumberUtil->getRegionCodeForNumber($parsedNumber));

            return [
                'country_code' => $countryCode,
                'country_short_name' => $countryShortName,
            ];
        } catch (\libphonenumber\NumberFormatException $e) {
            // Handle any exceptions if the phone number is not valid
            return null;
        }
    }

    function removeCountryCodeFromPhoneNumber($phoneNumber)
    {
        // Define a regular expression pattern to match the country code
        $pattern = '/^\+(\d{1,4})/'; // This pattern matches a '+' sign followed by 1 to 4 digits (the country code).

        // Use preg_replace to remove the country code
        $phoneNumberWithoutCountryCode = preg_replace($pattern, '', $phoneNumber);

        return $phoneNumberWithoutCountryCode;
    }

    public function sendAssigntoUserNotification($requestArr = [])
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        /*$users = User::whereNotNull('device_key')->where('id',1)->select('device_key','mobile_device_key', 'id', 'company_id')->get();*/
        $serverKey = 'AAAAsImurqQ:APA91bEqYpdInZ9unkqrVIuF_GTFJHSbWY3T611Kj8qXe_amTYZB4AWrAOBIwPnUGyyqndFH4wQn7DczaUZYzEDTizHL0-cB_CSEHFuvJuQGG6ZKa-deTDIZnogh1CWMopqGHEGXOuQX';

        $FcmToken = array();
        if ($requestArr['device_key']) {
            $FcmToken[] = $requestArr['device_key'];
        }

        if ($requestArr['mobile_device_key']) {
            $FcmToken[] = $requestArr['mobile_device_key'];
        }
        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => $requestArr['title'],
                "body" => $requestArr['body'],
                "sound" => 'notification_sound.wav',
                "icon" => url('assets/images/logo.png'),
                //"click_action" => url('/lead/timeline/' . $requestArr['customer_id'])
                //"click_action" => $requestArr['customer_id']
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
            "data" => [
                "type" => $requestArr['notification_type'],
                "lead_id" => $requestArr['customer_id']
            ],
            "priority" => 'high'
        ];
        $encodedData = json_encode($data);


        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
    }
}
//
//namespace App\Http\Controllers;
//
//use App\Models\Country;
//use App\Models\Customer;
//use App\Models\CustomerLead;
//use App\Models\EstimateTimeline;
//use App\Models\LeadAssignFbUser;
//use App\Models\LeadStage;
//use App\Models\User;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Http;
//use Illuminate\Support\Facades\Log;
//use Laravel\Socialite\Facades\Socialite;
//use libphonenumber\PhoneNumberUtil;
//use LogActivity;
//
//class FacebookController extends Controller
//{
//
//    /**
//     * Login Using Facebook
//     */
//    public function loginUsingFacebook()
//    {
//        return Socialite::driver('facebook')->redirect();
//    }
//
//    public function handleWebhook(Request $request)
//    {
//        $verifyToken = 'your_verification_token';
//
//        // Log the incoming request for debugging purposes
//        Log::channel('webhook')->info('Incoming webhook request', ['data' => $request->all()]);
//
//        if ($request->method() === 'GET' && $request->has('hub_mode') && $request->has('hub_verify_token')) {
//            $mode = $request->input('hub_mode');
//            $token = $request->input('hub_verify_token');
////            Log::channel('webhook')->info('Facebook', ['data' => $request]);
//            if ($mode === 'subscribe' && $token === $verifyToken) {
//                return response($request->input('hub_challenge'), 200)
//                    ->header('Content-Type', 'text/plain');
//            } else {
//                return response('Invalid Verify Token', 403);
//            }
//        }
//
//        $input = $request->getContent();
//        $data = json_decode($input, true);
//
//// Log the decoded data for debugging purposes
//        Log::channel('webhook')->info('Decoded webhook data', ['data' => $data]);
//
//        if (!empty($data) && isset($data['entry']) && !is_null($data['entry'])) {
//            // Process the events, e.g., handle leadgen events
//            $i = 0;
//            foreach ($data['entry'] as $entry) {
//                $i++;
//                $page_id = $entry['id'];
//                $facebookPageInfos = \DB::table('facebook_pages')->where('page_id', '=', $page_id)->select("page_id","user_id","company_id")->get();
//                if($facebookPageInfos->count() > 0) {
//                    foreach ($facebookPageInfos as $facebookPageInfo) {
//                        Log::channel('webhook')->info('facebookPageInfo webhook data', ['data' => $facebookPageInfo]);
//                        $user1 = \DB::table('users')->where('id', '=', $facebookPageInfo->company_id)->select("*")->first();
//                        $accessToken = $user1->facebook_token;
//                        foreach ($entry['changes'] as $change) {
//                            Log::channel('webhook')->info('change webhook data', ['data' => $change]);
//                            if ($change['field'] === 'leadgen') {
//                                $leadData = $change['value'];
//                                $formId = $leadData['form_id'];
//                                $formName = '';
//                                $formUrl = "https://graph.facebook.com/v20.0/$formId?fields=name&access_token=$accessToken";
//
//                                $formResponse = Http::get($formUrl);
//
//                                if ($formResponse->successful()) {
//                                    $formData = $formResponse->json();
//                                    if (isset($formData['name'])) {
//                                        $formName = $formData['name'];
//                                    }
//                                }
//
//                                $leadgenId = $leadData['leadgen_id'];
//                                $pageId = $leadData['page_id'];
//                                $pageName = '';
//                                $pageUrl = "https://graph.facebook.com/v20.0/$pageId?fields=name&access_token=$accessToken";
//                                $pageResponse = Http::get($pageUrl);
//
//                                if ($pageResponse->successful()) {
//                                    $pageData = $pageResponse->json();
//                                    if (isset($pageData['name'])) {
//                                        $pageName = $pageData['name'];
//                                    }
//                                }
//
//                                $user = DB::table('users')->where('id', '=', $facebookPageInfo->company_id)->select("*")->first();
//                                $access_token = $user->facebook_token;
//                                $company_id = ($user->company_id) ? $user->company_id : $user->id;
//                                $logged_user_company = User::select(["id","company_id","lead_merge_flag"])->where('id',$company_id)->first();
//                                $userId = $user->id;
//                                if (Customer::where('leadgen_id', '=', $leadgenId)->where('company_id', $company_id)->count() == 0) {
//
//                                    // Facebook
//                                    $lead_id = CustomerLead::select('id')->where(function ($query) use ($company_id) {
//                                        $query->where('name', 'Facebook');
//
//                                        $query->where('company_id', $company_id);
//                                        $query->where('status', '=', 0);
//                                    })->first();
//
//                                    if (empty($lead_id)) {
//                                        $category_array = [
//                                            'name' => 'Facebook',
//                                            'user_id' => $company_id,
//                                            'company_id' => $company_id
//                                        ];
//                                        $customerLeadId = CustomerLead::create($category_array);
//                                        $customer_lead_id = $customerLeadId['id'];
//                                    } else {
//                                        $customer_lead_id = $lead_id['id'];
//                                    }
//
//
//                                    $response = Http::get("https://graph.facebook.com/v20.0/$formId", [
////                            'fields' => 'field_data,ad_name,campaign_name',
//                                        'fields' => 'leads',
//                                        'access_token' => $access_token,
//                                    ]);
//
//                                    $dataObjs = $response->json();
//                                    if (array_key_exists('leads', $dataObjs)) {
//                                        DB::enableQueryLog();
//                                        $lastAssignedUser = LeadAssignFbUser::join("users", 'lead_assign_fb_users.user_id', '=', 'users.id')->where('lead_assign_fb_users.company_id', $company_id)->where('lead_assign_fb_users.assigned_list', 0)->select('lead_assign_fb_users.user_id', 'users.name', 'lead_assign_fb_users.company_id', 'lead_assign_fb_users.id', 'lead_assign_fb_users.assigned_list', 'users.device_key', 'users.mobile_device_key')->orderBy('lead_assign_fb_users.assigned_list', 'ASC')->orderBy('lead_assign_fb_users.id', 'ASC')->first();
//                                        Log::channel('webhook')->info('query_1', ["query_1" => DB::getQueryLog($lastAssignedUser)]);
//                                        if (!$lastAssignedUser) {
//                                            DB::enableQueryLog();
//                                            LeadAssignFbUser::where('company_id', $company_id)->update(['assigned_list' => 0]);
//                                            $lastAssignedUser = LeadAssignFbUser::join("users", 'lead_assign_fb_users.user_id', '=', 'users.id')->where('lead_assign_fb_users.company_id', $company_id)->where('lead_assign_fb_users.assigned_list', 0)->select('lead_assign_fb_users.user_id', 'users.name', 'lead_assign_fb_users.company_id', 'lead_assign_fb_users.id', 'lead_assign_fb_users.assigned_list', 'users.device_key', 'users.mobile_device_key')->orderBy('lead_assign_fb_users.assigned_list', 'ASC')->orderBy('lead_assign_fb_users.id', 'ASC')->first();
//                                            Log::channel('webhook')->info('query_2', ["query_2" => DB::getQueryLog($lastAssignedUser)]);
//                                        }
//
//                                        Log::channel('webhook')->info('facebookPageInfo webhook data', ['data' => $facebookPageInfo]);
////                        if ($dataObjs['leads']) {
//                                        $output = [];
//                                        foreach ($dataObjs['leads']['data'][0]['field_data'] as $field) {
//                                            $fieldName = $field['name'];
//                                            $fieldValue = $field['values'][0];
//                                            $output[$fieldName] = $fieldValue;
//                                        }
//                                        Log::channel('webhook')->info('Test_' . $userId . '_' . $company_id, ['data' => $dataObjs['leads']['data'][0]['field_data']]);
//                                        // Print the output in the desired format
//                                        $insertArr = [];
//                                        $description = '';
//                                        foreach ($output as $fieldName => $fieldValue) {
//                                            if ($fieldName == 'full_name' || $fieldName == 'full name' || $fieldName == 'name')
//                                                $insertArr['name'] = $fieldValue;
//                                            if ($fieldName == 'phone_number') {
//                                                $mobileNumber = $fieldValue; // Replace this with the mobile number you want to parse
//                                                $insertArr['phone_no'] = $fieldValue;
////                                                $pattern = '/^(\+?\d{1,4})?[\s-]?\(?\d{1,4}\)?[\s-]?\d{3,4}[\s-]?\d{3,4}$/';
//                                                $pattern = '/^\+\d{1,4}[\s-]?\(?\d{1,4}\)?[\s-]?\d{3,4}[\s-]?\d{3,4}$/';
//                                                if (preg_match($pattern, $fieldValue)) {
//                                                    $countryInfo = $this->getCountryInfoFromMobileNumber($fieldValue);
//                                                } else {
//                                                    $countryInfo = [];
//                                                    Log::channel('webhook')->info('Invalid_mobile_format_' . $userId . '_' . $company_id, ['data' => $fieldValue]);
//                                                }
//                                                Log::channel('webhook')->info('Mobile_no_' . $userId . '_' . $company_id, ['data' => $countryInfo, "testing mobile no" > $fieldValue]);
////                                                if (!empty($countryInfo) && $countryInfo !== null) {
//                                                if (!empty($countryInfo)) {
//                                                    $insertArr['country_code'] = "+{$countryInfo['country_code']}";
//                                                    $insertArr['whatsapp_country_code'] = "+{$countryInfo['country_code']}";
//                                                    $country = Country::where('sortname', $countryInfo['country_short_name'])->first();
//                                                    if ($country) {
//                                                        $insertArr['country_id'] = $country->id;
//                                                        $insertArr['currency_name'] = $country->currency_code;
//                                                        $insertArr['phone_no_country_id'] = $country->id;
//                                                        $insertArr['whatsapp_no_country_id'] = $country->id;
//                                                        $insertArr['currency_name_country_id'] = $country->id;
//                                                    }
//
//                                                    $phoneNumberWithoutCountryCode = substr($mobileNumber, strlen("+{$country->phonecode}"));
//                                                    $insertArr['phone_no'] = $phoneNumberWithoutCountryCode;
//                                                    $insertArr['whatsapp_no'] = $phoneNumberWithoutCountryCode;
//                                                }
//
//
//                                            }
//                                            if (!($fieldName == 'full_name' || $fieldName == 'phone_number'))
//                                                $description .= "$fieldName: $fieldValue\n";
//                                        }
//                                        $description .= "Form Name: $formName\n";
//                                        $description .= "Page Name: $pageName\n";
//                                        $description .= "Leadgen Id: $leadgenId\n";
//
//                                        if (!array_key_exists('country_code', $insertArr))
//                                            $insertArr['country_code'] = '+91';
//                                        if (!array_key_exists('whatsapp_country_code', $insertArr))
//                                            $insertArr['whatsapp_country_code'] = '+91';
//                                        $insertArr['description'] = $description;
//
//                                        if (!array_key_exists('currency_name', $insertArr))
//                                            $insertArr['currency_name'] = 'INR';
//                                        if (!array_key_exists('country_id', $insertArr))
//                                            $insertArr['country_id'] = 101;
//                                        if (!array_key_exists('phone_no_country_id', $insertArr))
//                                            $insertArr['phone_no_country_id'] = 101;
//                                        if (!array_key_exists('whatsapp_no_country_id', $insertArr))
//                                            $insertArr['whatsapp_no_country_id'] = 101;
//                                        if (!array_key_exists('currency_name_country_id', $insertArr))
//                                            $insertArr['currency_name_country_id'] = 101;
//                                        $insertArr['new_lead_flag'] = 1;
//                                        $insertArr['customer_lead_id'] = $customer_lead_id;
//                                        $insertArr['user_id'] = $userId;
//                                        $insertArr['company_id'] = $company_id;
//
//                                        $assigned_to_user = $lastAssignedUser->user_id;
//                                        $assigned_to_user_name = $lastAssignedUser->name;
//                                        $device_key = $lastAssignedUser->device_key;
//                                        $mobile_device_key = $lastAssignedUser->mobile_device_key;
//                                        $existData = Customer::where('phone_no', '=', $insertArr['phone_no'])
//                                            ->where('company_id', $company_id)
//                                            ->orderBy('id','DESC')
//                                            ->first();
//                                        if($existData){
//                                            $assigned_to_user = $existData->assigned_to_user;
//                                            DB::enableQueryLog();
//                                            $exist_user1 = User::select(["id", "name", "device_key", "mobile_device_key"])->where('id', $assigned_to_user)->first(); //->where('company_id', $company_id)
//
////                                            Log::channel('webhook')->info('Facebook', ['data' => DB::getQueryLog($exist_user1)]);
//                                            $assigned_to_user_name = $exist_user1->name;
//                                            $device_key = $exist_user1->device_key;
//                                            $mobile_device_key = $exist_user1->mobile_device_key;
//                                        }
//
//                                        $insertArr['assigned_to_user'] = $user1->facebook_integration == "Unassigned" ? '' : $assigned_to_user;
//                                        $insertArr['leadgen_id'] = $leadgenId;
//
//                                        $insertArr['lead_stage_id'] = 0;
//                                        $lead_stage_data = LeadStage::where('company_id',$company_id)->where('is_default',1)->select('name','id')->first();
//                                        if($lead_stage_data){
//                                            $insertArr['lead_stage_id'] =$lead_stage_data->id;
//                                        }
//                                        $existData='';
//                                        if (array_key_exists('phone_no', $insertArr)) {
//                                            $existData = Customer::where('phone_no', '=', $insertArr['phone_no'])
//                                                ->where('company_id', $company_id)
//                                                ->orderBy('id','DESC')
//                                                ->first();
//                                        }
//
//                                        if(!$existData || $logged_user_company->lead_merge_flag==0) {
//                                            $customer = Customer::create($insertArr);
//                                            $ids = $customer->id;
//
//                                            $logInput['assigned_to'] = $company_id;
//                                            $logInput['activity_type'] = 6;
//                                            $logInput['internal_remarks'] = "Lead added from Facebook";
//                                            $logInput['customer_id'] = $ids;
//                                            $logInput['entry_type'] = "leads";
//
//                                            $logInput['user_id'] = $userId;
//                                            $logInput['company_id'] = $company_id;
//                                            $logInput['created_by'] = $company_id;
//                                            $logInput['updated_by'] = $company_id;
//                                            LogActivity::addToActivityLogGuest($logInput);
//
//                                            if ($user1->facebook_integration != "Unassigned") {
//                                                $logInput['activity_type'] = 8;
//                                                $logInput['entry_type'] = "assigned";
//                                                $logInput['internal_remarks'] = "Assigned to " . $lastAssignedUser->name;
//                                                LogActivity::addToActivityLogGuest($logInput);
//
//                                                $noficationArr['customer_id'] = $ids;
//                                                $noficationArr['notification_type'] = "assign_to_you";
//                                                $noficationArr['device_key'] = $device_key;
//                                                $noficationArr['mobile_device_key'] = $mobile_device_key;
//                                                $noficationArr['title'] = 'New Lead Assigned To You';
//                                                $noficationArr['body'] = $insertArr['name'] . ' is assigned to you by ' . $lastAssignedUser->name;
//                                                $this->sendAssigntoUserNotification($noficationArr);
//                                            }
//                                            Log::channel('webhook')->info('Before Lead Merge' . $userId . '_' . $company_id, ['data' => $logInput, "testing mobile no" > $fieldValue]);
//
//                                        }
//
//                                        if ($existData && $logged_user_company->lead_merge_flag == 1) {
//                                            Log::channel('webhook')->info('Before Lead Merge Middle' . $userId . '_' . $company_id, ['data' => $logInput, "testing mobile no" > $fieldValue]);
//                                            $existDatafollowup = EstimateTimeline::where('customer_id', '=', $existData->id)
//                                                ->where('company_id', $company_id)
//                                                ->select('follow_up_datetime')
//                                                ->orderBy('id','DESC')
//                                                ->first();
//                                            $logInput['follow_up_datetime'] = $existDatafollowup->follow_up_datetime;
//                                            $logInput['assigned_to'] = $assigned_to_user;
//                                            $logInput['activity_type'] = 20;
//                                            $logInput['entry_type'] = "merge";
//                                            $logInput['activity_name'] = "Lead Merge";
//                                            $logInput['internal_remarks'] = $description;
//                                            $logInput['activity_notes'] = $description;
//                                            $logInput['customer_id'] = $existData->id;
//                                            $logInput['user_id'] = $company_id;
//                                            $logInput['company_id'] = $company_id;
//                                            $logInput['created_by'] = $company_id;
//                                            $logInput['updated_by'] = $company_id;
//                                            LogActivity::addToActivityLogGuest($logInput);
//                                            Log::channel('webhook')->info('Before Lead Merge Last' . $userId . '_' . $company_id, ['data' => $logInput, "testing mobile no" > $fieldValue]);
//                                            $noficationArr['customer_id'] = $existData->id;
//                                            $noficationArr['notification_type'] = "assign_to_you";
//                                            $noficationArr['device_key'] = $device_key;
//                                            $noficationArr['mobile_device_key'] = $mobile_device_key;
//                                            //$noficationArr['mobile_device_key'] = null;
//                                            $noficationArr['title'] = 'Lead Merge';
//                                            $noficationArr['body'] = $insertArr['name'] . ' is assigned to you by ' . $assigned_to_user_name;
//                                            $this->sendAssigntoUserNotification($noficationArr);
//                                        }
//
//                                        /*$customer = Customer::create($insertArr);
//                                        $ids = $customer->id;
//
//                                        $logInput['assigned_to'] = $company_id;
//                                        $logInput['activity_type'] = 6;
//                                        $logInput['internal_remarks'] = "Lead added from Facebook";
//                                        $logInput['customer_id'] = $ids;
//                                        $logInput['entry_type'] = "leads";
//
//                                        $logInput['user_id'] = $userId;
//                                        $logInput['company_id'] = $company_id;
//                                        $logInput['created_by'] = $company_id;
//                                        $logInput['updated_by'] = $company_id;
//                                        LogActivity::addToActivityLogGuest($logInput);
//
//                                        if ($user1->facebook_integration != "Unassigned") {
//                                            $logInput['activity_type'] = 8;
//                                            $logInput['entry_type'] = "assigned";
//                                            $logInput['internal_remarks'] = "Assigned to " . $lastAssignedUser->name;
//                                            LogActivity::addToActivityLogGuest($logInput);
//
//                                            $noficationArr['customer_id'] = $ids;
//                                            $noficationArr['notification_type'] = "assign_to_you";
//                                            $noficationArr['device_key'] = $lastAssignedUser->device_key;
//                                            $noficationArr['mobile_device_key'] = $lastAssignedUser->mobile_device_key;
//                                            $noficationArr['title'] = 'New Lead Assigned To You';
//                                            $noficationArr['body'] = $insertArr['name'] . ' is assigned to you by ' . $lastAssignedUser->name;
//                                            $this->sendAssigntoUserNotification($noficationArr);
//                                        }*/
//
//                                        LeadAssignFbUser::where('company_id', $company_id)->where("user_id", $assigned_to_user)->update(['assigned_list' => 1]);
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }
//        return response("Facebook Received data successfully", 200);
//    }
//
//    public function getCountryInfoFromMobileNumber($phoneNumber)
//    {
//        $phoneNumberUtil = PhoneNumberUtil::getInstance();
//
//        try {
//            $parsedNumber = $phoneNumberUtil->parse($phoneNumber, null);
//
//            // Get the country code
//            $countryCode = $phoneNumberUtil->getCountryCodeForRegion(
//                $phoneNumberUtil->getRegionCodeForNumber($parsedNumber)
//            );
//
//            // Get the country short name (ISO code)
//            $countryShortName = strtoupper($phoneNumberUtil->getRegionCodeForNumber($parsedNumber));
//
//            return [
//                'country_code' => $countryCode,
//                'country_short_name' => $countryShortName,
//            ];
//        } catch (\libphonenumber\NumberFormatException $e) {
//            // Handle any exceptions if the phone number is not valid
//            return null;
//        }
//    }
//
//    function removeCountryCodeFromPhoneNumber($phoneNumber)
//    {
//        // Define a regular expression pattern to match the country code
//        $pattern = '/^\+(\d{1,4})/'; // This pattern matches a '+' sign followed by 1 to 4 digits (the country code).
//
//        // Use preg_replace to remove the country code
//        $phoneNumberWithoutCountryCode = preg_replace($pattern, '', $phoneNumber);
//
//        return $phoneNumberWithoutCountryCode;
//    }
//
//    public function sendAssigntoUserNotification($requestArr = [])
//    {
//        $url = 'https://fcm.googleapis.com/fcm/send';
//        /*$users = User::whereNotNull('device_key')->where('id',1)->select('device_key','mobile_device_key', 'id', 'company_id')->get();*/
//        $serverKey = 'AAAAsImurqQ:APA91bEqYpdInZ9unkqrVIuF_GTFJHSbWY3T611Kj8qXe_amTYZB4AWrAOBIwPnUGyyqndFH4wQn7DczaUZYzEDTizHL0-cB_CSEHFuvJuQGG6ZKa-deTDIZnogh1CWMopqGHEGXOuQX';
//
//        $FcmToken = array();
//        if ($requestArr['device_key']) {
//            $FcmToken[] = $requestArr['device_key'];
//        }
//
//        if ($requestArr['mobile_device_key']) {
//            $FcmToken[] = $requestArr['mobile_device_key'];
//        }
//        $data = [
//            "registration_ids" => $FcmToken,
//            "notification" => [
//                "title" => $requestArr['title'],
//                "body" => $requestArr['body'],
//                "sound" => 'notification_sound.wav',
//                "icon" => url('assets/images/logo.png'),
//                //"click_action" => url('/lead/timeline/' . $requestArr['customer_id'])
//                //"click_action" => $requestArr['customer_id']
//                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
//            ],
//            "data" => [
//                "type" => $requestArr['notification_type'],
//                "lead_id" => $requestArr['customer_id']
//            ],
//            "priority" => 'high'
//        ];
//        $encodedData = json_encode($data);
//
//
//        $headers = [
//            'Authorization:key=' . $serverKey,
//            'Content-Type: application/json',
//        ];
//
//        $ch = curl_init();
//
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
//        $result = curl_exec($ch);
//        if ($result === FALSE) {
//            die('Curl failed: ' . curl_error($ch));
//        }
//        curl_close($ch);
//    }
//}

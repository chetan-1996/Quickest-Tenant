<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Helpers\LogActivity;
use App\Models\Country;
use App\Models\Customer;
use App\Models\CustomerLead;
use App\Models\FacebookPage;
use App\Models\Indiamart_api_tokens;
use App\Models\Lead_assign_users;
use App\Models\LeadAssignFbUser;
use App\Models\LeadAssignTiUser;
use App\Models\PlanHistory;
use App\Models\State;
use App\Models\TradeindiaApiToken;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;
use Facebook\Facebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IntegrationController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->main_company = User::select(["follow_up_note_req_flg"])
                ->where('id', $this->company_id)->first();
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }

    public function index()
    {
        $user_data = User::where('id', $this->company_id)->select(['whatsapp_auth_token','whatsapp_open_chat_token','follow_up_note_req_flg', 'indiamart_integration', 'facebook_integration', 'facebook_email', 'facebook_id','tradeindia_integration'])->first();
        $india_mart_data = Indiamart_api_tokens::select(["id", "indiamart_token"])->where('user_id', $this->company_id)->first();
        $tradeindia_data = TradeindiaApiToken::select(["id", "tradeindia_token"])->where('user_id', $this->company_id)->first();
        $segment = $this->segment;
        return view('app.integration', compact('user_data', 'india_mart_data','tradeindia_data', 'segment'));
    }

    public function facebook_integration()
    {
        return view('facebook_integration');
    }

    public function fb_subscib(){
        $fb = new Facebook([
            'app_id' => config('services.facebook.client_id'),
            'app_secret' => config('services.facebook.client_secret'),
            'default_graph_version' => 'v20.0',
        ]);

        $user = \Auth::user();
        $accessToken = $user->facebook_token;

        $response = $fb->get('/me/accounts', $accessToken);
        $pages = $response->getGraphEdge()->asArray();
        foreach ($pages as $key => $page) {
            $pageId = $page['id'];
            $pageAccessToken = $page['access_token'];
            $response = $fb->get('/'.$pageId.'/subscribed_apps', $pageAccessToken);
            $subscriptions = $response->getDecodedBody();
        }

        dd($response);
    }

    public function fb_app(){

        $fb = new Facebook([
            'app_id' => config('services.facebook.client_id'),
            'app_secret' => config('services.facebook.client_secret'),
            'default_graph_version' => 'v20.0',
        ]);

        $accessToken = '725884809203550|28_83NRiueWMCFLsxMQFArcKPwo';
        $app_id = '725884809203550';

        $response = Http::get("https://graph.facebook.com/v20.0/{$app_id}/subscriptions", [
            'access_token' => $accessToken,
        ]);

        $responseData = $response->json();

        if (isset($responseData['error'])) {
            // Handle the error here
            $errorMessage = $responseData['error']['message'];
            return response()->json(['error' => $errorMessage], 500);
        }
        return response()->json(['subscriptions' => $responseData], 200);
    }

    public function facebookLeadsRouting()
    {

        $fb = new Facebook([
            'app_id' => config('services.facebook.client_id'),
            'app_secret' => config('services.facebook.client_secret'),
            'default_graph_version' => 'v20.0',
        ]);

        $user = User::where('id', $this->company_id)->select("*")->first();
        $accessToken = $user->facebook_token;
        $pages = [];
        if ($accessToken) {

            $app_id = '725884809203550';
            $responseAss = Http::post("https://graph.facebook.com/v20.0/{$app_id}/subscriptions", [
                'object' => "user",
                'access_token' => '725884809203550|28_83NRiueWMCFLsxMQFArcKPwo',
                'callback_url' => route('webhook'), //, ['user_id' => $user->id]
                // 'fields' => 'leadgen',
                'include_values' => 'true',
                'verify_token' => 'your_verification_token',
            ]);

            if ($responseAss->failed()) {
                // Log the error response for debugging
                dd($responseAss->body());
            }

            $responseAs = Http::post("https://graph.facebook.com/v20.0/{$app_id}/subscriptions", [
                'object' => "page",
                'access_token' => '725884809203550|28_83NRiueWMCFLsxMQFArcKPwo',
                'callback_url' => route('webhook'), //, ['user_id' => $user->id]
                'fields' => 'leadgen',
                'include_values' => 'true',
                'verify_token' => 'your_verification_token',
            ]);

            if ($responseAs->failed()) {
                // Log the error response for debugging
                dd($responseAs->body());
            }

            $responseData = $responseAs->json();

            if (isset($responseData['error'])) {
                // Handle the error here
                echo $errorMessage = $responseData['error']['message']."<br>";
                    // return response()->json(['error' => $errorMessage], 500);
            }
            /*$fb = new Facebook([
                'app_id' => config('services.facebook.client_id'),
                'app_secret' => config('services.facebook.client_secret'),
                'default_graph_version' => 'v20.0',
            ]);*/
            try {
                $response = $fb->get('/me/accounts', $accessToken);
                $pages = $response->getGraphEdge()->asArray();
                FacebookPage::where("company_id", $this->company_id)->delete();
                foreach ($pages as $key => $page) {
                    $pageId = $page['id'];
                    $pageAccessToken = $page['access_token'];


                    $subscribeUrl = "https://graph.facebook.com/v20.0/$pageId/subscribed_apps";
                    $responses= Http::post($subscribeUrl, [
                        // 'object' => 'page',
                        'access_token' => $pageAccessToken,
                        'subscribed_fields' => 'leadgen', // Subscribe to leadgen events
                        'callback_url' => route('webhook'), // Your webhook endpoint URL , ['user_id' => $user->id]
                        // 'verify_token' => "your_verification_token_".$user->id,
                        'verify_token' => "your_verification_token",
                        'include_values' => 'true',
                        // 'app_secret' => config('services.facebook.client_secret'),
                    ]);

                    /* if($responses->successful()){
                         User::where('id',$user->id)->update(['facebook_webhook_verify_token' => "your_verification_token_".$user->id]);
                     }*/

                     if ($responses->successful()) {
                         //echo "Leadgen webhook subscription successful.";
                     } else {
                         //echo "Failed to subscribe to the leadgen webhook.";
                     }

                    // $response = $fb->get("/$pageId/?fields=leadgen_forms{leads}", $pageAccessToken);
                    $response = $fb->get("/$pageId/?fields=leadgen_forms", $pageAccessToken);
                    $leadForms = $response->getGraphNode()->asArray();
                    $form_count = 0;
                    if ($leadForms) {
                        $pages[$key]['lead_forms'] = $leadForms;
                        $pages[$key]['lead_forms_name'] = (array_key_exists('leadgen_forms', $leadForms))?$leadForms['leadgen_forms']:[];
                        /*foreach ($leadForms as $leadForm){
                            $pages[$key]['lead_forms'] = $leadForm;
                        }*/
                        $form_count = (array_key_exists('leadgen_forms', $leadForms))?count($leadForms['leadgen_forms']):0;
                    }

                    $pageName = '';
                    $pageUrl = "https://graph.facebook.com/v20.0/$pageId?fields=name&access_token=$accessToken";
                    $pageResponse = Http::get($pageUrl);

                    if ($pageResponse->successful()) {
                        $pageData = $pageResponse->json();
                        if (isset($pageData['name'])) {
                            $pageName = $pageData['name'];
                        }
                    }
                    FacebookPage::create(["page_id" => $pageId, "page_name" =>$pageName, "form_count" => $form_count, "user_id"=>$this->logged_user->id,"company_id" => $this->company_id]);
                }

            } catch
            (\Facebook\Exceptions\FacebookResponseException $e) {
                User::where("id", "=", $this->company_id)->update(["facebook_email" => '', "facebook_id" => '', "facebook_token" => '', "facebook_name" => '']);
                return redirect()->route('auth-facebook');
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
                // Handle API errors.
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                // Handle SDK errors.
                User::where("id", "=", $this->company_id)->update(["facebook_email" => '', "facebook_id" => '', "facebook_token" => '', "facebook_name" => '']);
                return redirect()->route('auth-facebook');
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
        }
        //  dd($pages);
        $user_data = User::where('id', $this->company_id)->select(['follow_up_note_req_flg', 'indiamart_integration', 'facebook_integration', 'facebook_email', 'facebook_id','facebook_name'])->first();
        $segment = $this->segment;
        return view('app.facebook-integration', compact('pages','user_data', 'segment'));
    }

    public function facebookDiconnected()
    {
        $fb = new Facebook([
            'app_id' => config('services.facebook.client_id'),
            'app_secret' => config('services.facebook.client_secret'),
            'default_graph_version' => 'v20.0',
        ]);
        $app_secret = config('services.facebook.client_secret'); // Replace with your Facebook App Secret
        $access_token = 'your_access_token'; // Replace with your Page Access Token

        $user = User::where('id', $this->company_id)->select("*")->first();
        $accessToken = $user->facebook_token;

        $response = $fb->get('/me/accounts', $accessToken);
        $pages = $response->getGraphEdge()->asArray();
        foreach ($pages as $key => $page) {
            $pageId = $page['id'];
            $pageAccessToken = $page['access_token'];
            $unsubscribeUrl = 'https://graph.facebook.com/v20.0/me/subscribed_apps?access_token=' . $pageAccessToken;
            $response = Http::delete($unsubscribeUrl, [
                'app_secret' => $app_secret,
            ]);


            /*$response = $fb->delete(
                '/725884809203550/subscriptions',
                array (
                    'object' => 'page',
                ),
                '725884809203550|28_83NRiueWMCFLsxMQFArcKPwo'
            );*/
            /*if ($response->successful()) {
                return "Webhook subscription disconnected successfully.";
            } else {
                return "Failed to disconnect webhook subscription.";
            }*/

        }
        FacebookPage::where("company_id", $this->company_id)->delete();
        User::where("id", "=", $this->company_id)->update(["facebook_email" => '', "facebook_id" => '', "facebook_token" => '', "facebook_name" => '']);
        $fb->delete('/me/permissions', [], $accessToken);
        return response()->json(['success' => 'Facebook Disconnected!'], 201);
    }

    public function get_india_mart_apitoken()
    {

        $data = Indiamart_api_tokens::select(["id", "indiamart_token"])->where('user_id', $this->company_id)->first();

        return response()->json(['success' => 'Successfully saved!', 'data' => $data], 201);
    }

    public function india_mart_lead_verify(Request $request)
    {
        $data = $request->all();
        $currentTimeStamp = Carbon::now()->format('d-m-Y H:i:s');
        $fiveMinutesAgoTimeStamp = Carbon::now()->subMinutes(5)->addSecond(1)->format('d-m-Y H:i:s');
        // $currentTimeStamp = "01-08-2023";
        // $fiveMinutesAgoTimeStamp = "27-07-2023";
        $webhookUrl = 'https://mapi.indiamart.com/wservce/crm/crmListing/v2/?glusr_crm_key=' . $data['indiamart_token'] . '&start_time=' . $fiveMinutesAgoTimeStamp . '&end_time=' . $currentTimeStamp; // Replace with your actual webhook URL
        $response = Http::withOptions(['verify' => base_path('cacert.pem')])->post($webhookUrl);
        if ($response->successful()) {
            switch ($response['CODE']) {
                case '401':
                    if ($response['MESSAGE'] == "CRM key that you are using is incorrect. Kindly use the correct CRM key as provided in the email.") {
                        return response()->json(['errors' => $response['MESSAGE']], 400);
                    } else if (strpos($response['MESSAGE'], "will be available after 24 hours from") !== false) {
                        return response()->json(['errors' => $response['MESSAGE']], 400);
                    }
                    break;
                case '204':
                    $res = $this->import_lead($response, $this->company_id);
                    $value['user_id'] = $this->company_id;
                    $value['indiamart_token'] = $data['indiamart_token'];
                    $customer = Indiamart_api_tokens::updateOrCreate($value);
                    return response()->json([
                        'success' => 'Successfully Integrated!',
                        'india_mart_message' => $response['MESSAGE']
                    ], 201);
                    //return response()->json(['errors' => $response['MESSAGE']], 400);
                    break;
                default:
                    $res = $this->import_lead($response, $this->company_id);
                    $value['user_id'] = $this->company_id;
                    $value['indiamart_token'] = $data['indiamart_token'];
                    $customer = Indiamart_api_tokens::updateOrCreate($value);
                    return response()->json(['success' => 'Successfully Integrated!'], 201);
                    break;
            }
        } else {
            return $response->status();
        }
    }

    public function import_lead($request, $user_id)
    {
        if (!empty($request['RESPONSE'])) {
            $logged_user = User::select(["id", "company_id", "indiamart_integration", "name", "assigned_list"])->where('id', $user_id)->first();

            if ($logged_user) {

                $users = User::select(["id", "name"])->whereIn('id', explode(",", $logged_user->assigned_list))->where('invite_status', 1)->get()->toArray();
                $totalUsers = count($users);

                $company_id = $logged_user->company_id ? $logged_user->company_id : $logged_user->id;
                $lead_id = CustomerLead::select('id')->where(function ($query) use ($company_id) {
                    $query->where('name', 'IndiaMART');
                    $query->where('company_id', $company_id);
                    $query->where('status', '=', 0);
                })->first();

                $lastAssignedUser = Lead_assign_users::where('company_id', $company_id)->first();
                $new_user_list = [];

                //$lastAssignedUserIndex = 0;
                // foreach (explode(",",$lastAssignedUser['assigned_list']) as $key => $value) {
                //     array_push($new_user_list,User::select(["id","name"])->where('id', $value)->where('invite_status',1)->first()->toArray());
                // }
                // $totalUsersNew = count($new_user_list);

                if (empty($lead_id)) {
                    $category_array = [
                        'name' => 'IndiaMart',
                        'user_id' => $logged_user->id,
                        'company_id' => $company_id
                    ];
                    $customerLeadId = CustomerLead::create($category_array);
                    $customer_lead_id = $customerLeadId['id'];
                } else {
                    $customer_lead_id = $lead_id['id'];
                }
                $lead_data = [];
                $lastAssignedUserIndex = 0;
                if ($lastAssignedUser === null) {
                    $lastAssignedUser = 0;
                }
                $user = explode(",", $logged_user->assigned_list);
                $assignmentSetting = Lead_assign_users::firstOrNew();
                $lastAssignedIndex = $assignmentSetting['user_id'] ? $assignmentSetting['user_id'] : 0;

                $lastIndex = end($users);
                $lastIndex = key($users);

                if ($lastAssignedIndex == $lastIndex) {
                    $lastAssignedIndex = 0;
                } else {
                    if ($lastAssignedIndex == 0) {
                        $lastAssignedIndex = $assignmentSetting['user_id'] % count($users);
                    } else {
                        if ($lastAssignedIndex <= $lastIndex) {
                            $lastAssignedIndex = ($assignmentSetting['user_id'] + 1) % count($users);
                        } else {
                            $lastAssignedIndex = $lastAssignedIndex;
                        }
                    }
                }
                // foreach ($request['RESPONSE'] as $key => $lead) {
                //     if (isset($users[$lastAssignedIndex])) {
                //         $assignmentSetting['user_id'] = $lastAssignedIndex;
                //         $assignmentSetting['company_id'] = $company_id;
                //         $assignmentSetting->save();
                //         $lastAssignedIndex++;
                //     }
                // }exit;
                foreach ($request['RESPONSE'] as $key => $lead) {
                    //echo "<pre>";print_r($users[$lastAssignedIndex]['name']);exit;
                    // if($users->count()!=0){
                    //     $nextUserIndex = $lastAssignedUserIndex % $users->count();
                    //     $assignedUser = $users[$nextUserIndex];

                    //     $startUserIndex = ($lastAssignedUser + 1) % $totalUsers;
                    //     $assignedUserId = $users[$startUserIndex]['id'];
                    //     $assignedUserName = $users[$startUserIndex]['name'];
                    // }else{
                    //     $assignedUserId= $logged_user->id;
                    //     $assignedUserName = $logged_user->name;
                    // }
                    if (isset($users[$lastAssignedIndex])) {
                        $assignedUserId = $users[$lastAssignedIndex]['id'];
                        $assignedUserName = $users[$lastAssignedIndex]['name'];
                    } else {
                        $assignedUserId = $logged_user->id;
                        $assignedUserName = $logged_user->name;
                    }

                    if (!empty($lead['SENDER_COMPANY'])) {
                        $customer_type = "Business";
                    } else {
                        $customer_type = "Individual";
                    }
                    $sender_mobile = explode("-", $lead['SENDER_MOBILE']);
                    $countryCode = $sender_mobile[0];
                    $phone_no = $sender_mobile[1];
                    $logInput['internal_remarks'] = "Lead added from IndiaMart";
                    $lead_data = [
                        'assigned_to_user' => $logged_user->indiamart_integration == "Unassigned" ? '' : $assignedUserId,
                        'user_id' => $logged_user->id,
                        'company_id' => $company_id,
                        'name' => $lead['SENDER_NAME'],
                        'email' => $lead['SENDER_EMAIL'],
                        'phone_no' => $phone_no,
                        'pincode' => $lead['SENDER_PINCODE'],
                        'address' => $lead['SENDER_ADDRESS'],
                        'description' => $lead['QUERY_MESSAGE'],
                        'new_lead_flag' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'customer_lead_id' => $customer_lead_id,
                        'customer_type' => $customer_type,
                        'country_code' => $countryCode
                    ];

                    if ($lead['SENDER_STATE'] != "") {
                        $state = State::select(['id', 'country_id'])->where(function ($query) use ($lead) {
                            $query->where('name', $lead['SENDER_STATE']);
                        })->first();
                        if (!empty($state)) {
                            $lead_data['state_id'] = $state['id'];
                        }
                    }
                    $lead_data['country_id'] = "101";
                    if ($lead['SENDER_COUNTRY_ISO'] != "") {
                        $country = Country::select(['id'])->where(function ($query) use ($lead) {
                            $query->where('sortname', $lead['SENDER_COUNTRY_ISO']);
                        })->first();
                        if (!empty($country)) {
                            $lead_data['country_id'] = $country['id'];
                        }
                    }

                    $customer = Customer::create($lead_data);
                    $ids = $customer->id;
                    $logInput['activity_type'] = 6;
                    $logInput['customer_id'] = $ids;
                    $logInput['user_id'] = $logged_user->id;
                    $logInput['company_id'] = $company_id;
                    $logInput['created_by'] = $logged_user->id;
                    $logInput['updated_by'] = $logged_user->id;
                    LogActivity::addToActivityLogGuest($logInput);
                    if ($logged_user->indiamart_integration != "Unassigned") {
                        $logInput['activity_type'] = 8;
                        $logInput['entry_type'] = "assigned";
                        $logInput['internal_remarks'] = "Assigned to " . $assignedUserName;
                        LogActivity::addToActivityLogGuest($logInput);
                    }

                    if (isset($users[$lastAssignedIndex])) {
                        $assignmentSetting['user_id'] = $lastAssignedIndex;
                        $assignmentSetting['company_id'] = $company_id;
                        $assignmentSetting->save();
                        $lastAssignedIndex++;
                    } else {
                        $assignmentSetting['user_id'] = 0;
                        $assignmentSetting['company_id'] = $company_id;
                        $assignmentSetting->save();
                        $lastAssignedIndex++;
                    }
                }
                return 0;
            }
        }
        return response()->json(['success' => 'Successfully saved!'], 201);
    }

    // public function import_lead($request,$user_id){
    //     if(!empty($request['RESPONSE'])){
    //         $logged_user = User::select(["id","company_id","indiamart_integration","name"])->where('id',$user_id)->first();
    //         $users = User::select(["id"])->where('company_id', $user_id)->get();

    //         $lead_data=[];
    //         $lastAssignedUserIndex = 0;
    //         if($logged_user){
    //             foreach ($request['RESPONSE'] as $key => $lead) {
    //                 if($users->count()!=0){
    //                     $nextUserIndex = $lastAssignedUserIndex % $users->count();
    //                     $assignedUser = $users[$nextUserIndex];
    //                     $assignedUserId= $assignedUser['id'];
    //                 }else{
    //                     $assignedUserId= $logged_user->id;
    //                 }
    //                 $logInput['internal_remarks'] = "Lead added by IndiaMart";
    //                 $company_id = $logged_user->company_id?$logged_user->company_id:$logged_user->id;
    //                 $lead_data = [
    //                     'assigned_to_user' => $logged_user->indiamart_integration=="Unassigned"?'':$assignedUserId,
    //                     //'assigned_to_user' => $assignedUserId,
    //                     'user_id' => $logged_user->id,
    //                     'company_id' => $company_id,
    //                     'name'=> $lead['SENDER_NAME'],
    //                     'email'=> $lead['SENDER_EMAIL'],
    //                     'phone_no'=> $lead['SENDER_MOBILE'],
    //                     'pincode'=> $lead['SENDER_PINCODE'],
    //                     'address'=> $lead['SENDER_ADDRESS'],
    //                     'description'=> $lead['QUERY_MESSAGE'],
    //                     'new_lead_flag' => 1,
    //                     'created_at'=> date('Y-m-d H:i:s')
    //                 ];
    //                 $logInput['user_id'] =  $logged_user->id;
    //                 $logInput['company_id'] =  $company_id;
    //                 $logInput['created_by'] =  $logged_user->id;
    //                 $logInput['updated_by'] =  $logged_user->id;
    //                 EstimateTimelineModel::create($logInput);

    //                 $logInput['activity_type'] = 8;
    //                 $logInput['entry_type'] = "assigned";
    //                 $logInput['internal_remarks'] = "Assigned to " . $logged_user->name;
    //                 EstimateTimelineModel::create($logInput);

    //                 if($lead['SENDER_STATE']!=""){
    //                     $state = State::select(['id','country_id'])->where(function ($query) use ($lead) {
    //                         $query->where('name', $lead['SENDER_STATE']);
    //                     })->first();
    //                     if(!empty($state)){
    //                         $lead_data['state_id'] = $state['id'];
    //                     }
    //                 }
    //                 $lead_data['country_id'] = "101";
    //                 if($lead['SENDER_COUNTRY_ISO']!=""){
    //                     $country = Country::select(['id'])->where(function ($query) use ($lead) {
    //                         $query->where('sortname', $lead['SENDER_COUNTRY_ISO']);
    //                     })->first();
    //                     if(!empty($country)){
    //                         $lead_data['country_id'] = $country['id'];
    //                     }
    //                 }
    //                 $lastAssignedUserIndex++;
    //             }
    //             return Customer::insert($lead_data);
    //         }
    //     }
    //     return response()->json(['success' => 'Successfully saved!'], 201);
    // }

    public function india_mart_lead()
    {
        $currentTimeStamp = Carbon::now()->format('d-m-Y H:i:s');
        $fiveMinutesAgoTimeStamp = Carbon::now()->subMinutes(10)->addSecond(2)->format('d-m-Y H:i:s');
        $request['indiamart_token'] = "mR26ErBo5H/ASfet7neP7liNqlHNnDFjXw==";
        //$currentTimeStamp = "01-08-2023";
        //$fiveMinutesAgoTimeStamp = "28-07-2023";
        // $webhookUrl = 'https://mapi.indiamart.com/wservce/crm/crmListing/v2/?glusr_crm_key='.$request['indiamart_token'].'&start_time='.$fiveMinutesAgoTimeStamp.'&end_time='.$currentTimeStamp; // Replace with your actual webhook URL
        // $response = Http::withOptions(['verify' => base_path('cacert.pem')])->post($webhookUrl);

        $response['RESPONSE'] = [
            [
                'UNIQUE_QUERY_ID' => '2508565312',
                'QUERY_TYPE' => 'W',
                'QUERY_TIME' => '2023-07-31 09:58:33',
                'SENDER_NAME' => 'Brijesh Jani',
//                'SENDER_MOBILE' => '+91-9106522144',
                'SENDER_MOBILE' => '+91-4455121574',
                'SENDER_EMAIL' => 'thebrijeshjani@gmail.com',
                'SUBJECT' => 'Requirement for Solar Panel Manufacturer',
                'SENDER_COMPANY' => 'D jani Solar',
                'SENDER_ADDRESS' => 'Surat, Gujarat,         395009',
                'SENDER_CITY' => 'Surat',
                'SENDER_STATE' => 'Gujarat',
                'SENDER_PINCODE' => '395009',
                'SENDER_COUNTRY_ISO' => 'IN',
                'SENDER_MOBILE_ALT' => '',
                'SENDER_PHONE' => '',
                'SENDER_PHONE_ALT' => '',
                'SENDER_EMAIL_ALT' => '',
                'QUERY_PRODUCT_NAME' => 'Solar Panel Manufacturer',
                'QUERY_MESSAGE' => 'I want to buy Solar Panel Manufacturer. Kindly send me price and other details. Type : Monocrystalline Quantity : 150 Quantity Unit : MW Probable Requirement Type : Business Use',
                'QUERY_MCAT_NAME' => 'Solar Panels',
                'CALL_DURATION' => '',
                'RECEIVER_MOBILE' => '',
            ],
            [
                'UNIQUE_QUERY_ID' => '76328751',
                'QUERY_TYPE' => 'P',
                'QUERY_TIME' => '2023-07-31 10:38:38',
                'SENDER_NAME' => 'Jaydeep Tank',
                //                'SENDER_MOBILE' => '+91-9106522144',
                'SENDER_MOBILE' => '+91-4455121575',
                'SENDER_EMAIL' => 'thebrijeshjani@gmail.com',
                'SUBJECT' => 'Buyer Call',
                'SENDER_COMPANY' => 'D jani Solar',
                'SENDER_ADDRESS' => 'Surat, Gujarat,         395009',
                'SENDER_CITY' => 'Surat',
                'SENDER_STATE' => 'Gujarat',
                'SENDER_PINCODE' => '395009',
                'SENDER_COUNTRY_ISO' => 'IN',
                'SENDER_MOBILE_ALT' => '',
                'SENDER_PHONE' => '',
                'SENDER_PHONE_ALT' => '',
                'SENDER_EMAIL_ALT' => '',
                'QUERY_PRODUCT_NAME' => '',
                'QUERY_MESSAGE' => '',
                'QUERY_MCAT_NAME' => '',
                'CALL_DURATION' => '152',
//                'RECEIVER_MOBILE' => '7861813600',
                'RECEIVER_MOBILE' => '',
            ],
            // [
            //     'UNIQUE_QUERY_ID' => '76328751',
            //     'QUERY_TYPE' => 'P',
            //     'QUERY_TIME' => '2023-07-31 10:38:38',
            //     'SENDER_NAME' => 'Chetan Mordiya',
            //     'SENDER_MOBILE' => '+91-9106522144',
            //     'SENDER_EMAIL' => 'thebrijeshjani@gmail.com',
            //     'SUBJECT' => 'Buyer Call',
            //     'SENDER_COMPANY' => 'D jani Solar',
            //     'SENDER_ADDRESS' => 'Surat, Gujarat,         395009',
            //     'SENDER_CITY' => 'Surat',
            //     'SENDER_STATE' => 'Gujarat',
            //     'SENDER_PINCODE' => '395009',
            //     'SENDER_COUNTRY_ISO' => 'IN',
            //     'SENDER_MOBILE_ALT' => '',
            //     'SENDER_PHONE' => '',
            //     'SENDER_PHONE_ALT' => '',
            //     'SENDER_EMAIL_ALT' => '',
            //     'QUERY_PRODUCT_NAME' => '',
            //     'QUERY_MESSAGE' => '',
            //     'QUERY_MCAT_NAME' => '',
            //     'CALL_DURATION' => '152',
            //     'RECEIVER_MOBILE' => '7861813600',
            // ]
        ];
        $this->import_lead_new($response, '1');
        // if ($response->successful()) {
        //     switch ($response['CODE']) {
        //         case '401':
        //             if($response['MESSAGE']=="CRM key that you are using is incorrect. Kindly use the correct CRM key as provided in the email."){
        //                 return response()->json(['errors' => $response['MESSAGE']], 400);
        //             }else if (strpos($response['MESSAGE'], "will be available after 24 hours from") !== false) {
        //                 return response()->json(['errors' => $response['MESSAGE']], 400);
        //             }
        //             break;
        //         case '204':
        //             $res = $this->import_lead($response,'53');
        //             return response()->json(['success' => 'Successfully Integrated!'], 201);
        //             break;
        //         default:
        //             $res = $this->import_lead($response,'53');
        //             return response()->json(['success' => 'Successfully Integrated!'], 201);
        //             break;
        //     }
        // } else {
        //     return $response->status();
        // }
    }

    public function import_lead_new($request, $user_id)
    {
        if(!empty($request['RESPONSE'])){
            $logged_user = User::select(["id","company_id","indiamart_integration","name","assigned_list"])->where('id',$user_id)->first();
            if($logged_user){
                $company_id = $logged_user->company_id?$logged_user->company_id:$logged_user->id;
                $lead_id = CustomerLead::select('id')->where(function ($query) use ($company_id) {
                    $query->where('name', 'IndiaMART');

                    $query->where('company_id', $company_id);
                    $query->where('status', '=', 0);
                })->first();

                if(empty($lead_id)){
                    $category_array=[
                        'name'=>'IndiaMart',
                        'user_id'=>$logged_user->id,
                        'company_id'=>$company_id
                    ];
                    $customerLeadId = CustomerLead::create($category_array);
                    $customer_lead_id = $customerLeadId['id'];
                }else{
                    $customer_lead_id = $lead_id['id'];
                }

                $last_user = 0;
                foreach ($request['RESPONSE'] as $key => $lead) {
                    $lastAssignedUser = Lead_assign_users::join("users", 'lead_assign_users.user_id', '=', 'users.id')->where('lead_assign_users.company_id',$company_id)->where('lead_assign_users.assigned_list',0)->select('lead_assign_users.user_id','users.name','lead_assign_users.company_id','lead_assign_users.id','lead_assign_users.assigned_list','users.device_key','users.mobile_device_key')->orderBy('lead_assign_users.assigned_list','ASC')->orderBy('lead_assign_users.id','ASC')->first();

                    if(!$lastAssignedUser){
                        Lead_assign_users::where('company_id',$company_id)->update(['assigned_list'=>0]);
                        $lastAssignedUser = Lead_assign_users::join("users", 'lead_assign_users.user_id', '=', 'users.id')->where('lead_assign_users.company_id',$company_id)->where('lead_assign_users.assigned_list',0)->select('lead_assign_users.user_id','users.name','lead_assign_users.company_id','lead_assign_users.id','lead_assign_users.assigned_list','users.device_key','users.mobile_device_key')->orderBy('lead_assign_users.assigned_list','ASC')->orderBy('lead_assign_users.id','ASC')->first();
                    }


                    $assignedUserId= $logged_user->id;
                    $assignedUserName = $logged_user->name;

                    if(!empty($lead['SENDER_COMPANY'])){
                        $customer_type = "Business";
                    }else{
                        $customer_type = "Individual";
                    }
                    $sender_mobile = explode("-", $lead['SENDER_MOBILE']);
                    $countryCode = $sender_mobile[0];
                    $phone_no = $sender_mobile[1];
                    $logInput['internal_remarks'] = "Lead added from IndiaMart";

                    $assigned_to_user = $lastAssignedUser->user_id;
                    if($logged_user->indiamart_integration=="Unassigned"){
                        $assigned_to_user = '';
                    }

                    if($logged_user->indiamart_integration=="Round-Robin"){
                        $assigned_to_user = $lastAssignedUser->user_id;
                    }

                    if($logged_user->indiamart_integration=="Others"){

                        $existData = Customer::where('phone_no', '=', $phone_no)
                            ->where('company_id', $company_id)
                            /*->where(function ($query) use ($id) {
                                if ($id != 0) {
                                    $query->Where(function ($query) use ($id) {
                                        $query->where('id', '!=', $id);
                                    });
                                }
                            })*/
                            ->orderBy('id','DESC')
                            ->first();

                        if($existData){
                            $assigned_to_user = $existData->assigned_to_user;
                        }

                        $exist_user = User::select(["id"])->where('mobile_no',$lead['RECEIVER_MOBILE'])->first();
                        if($exist_user){
                            $assigned_to_user = $exist_user->id;
                        }

                    }

                    $lead_data = [
//                        'assigned_to_user' => $logged_user->indiamart_integration=="Unassigned"?'':$lastAssignedUser->user_id,
                        'assigned_to_user' => $assigned_to_user,
                        'user_id' => $logged_user->id,
                        'company_id' => $company_id,
                        'name'=> $lead['SENDER_NAME'],
                        'email'=> $lead['SENDER_EMAIL'],
                        'phone_no'=> $phone_no,
                        'pincode'=> $lead['SENDER_PINCODE'],
                        'address'=> $lead['SENDER_ADDRESS'],
                        'description'=> $lead['QUERY_MESSAGE'],
                        'new_lead_flag' => 1,
                        'created_at'=> date('Y-m-d H:i:s'),
                        'customer_lead_id'=>$customer_lead_id,
                        'customer_type'=>$customer_type,
                        'country_code'=>$countryCode,
                        'whatsapp_country_code'=>$countryCode,
                        'whatsapp_no'=> $phone_no,
                    ];

                    if($lead['SENDER_STATE']!=""){
                        $state = State::select(['id','country_id'])->where(function ($query) use ($lead) {
                            $query->where('name', $lead['SENDER_STATE']);
                        })->first();
                        if(!empty($state)){
                            $lead_data['state_id'] = $state['id'];
                        }
                    }
                    $lead_data['country_id'] = "101";
                    if($lead['SENDER_COUNTRY_ISO']!=""){
                        $country = Country::select(['id'])->where(function ($query) use ($lead) {
                            $query->where('sortname', $lead['SENDER_COUNTRY_ISO']);
                        })->first();
                        if(!empty($country)){
                            $lead_data['country_id'] = $country['id'];
                        }
                    }

                    $customer = Customer::create($lead_data);
                    $ids = $customer->id;
                    $logInput['activity_type'] = 6;
                    $logInput['customer_id'] = $ids;
                    $logInput['user_id'] =  $logged_user->id;
                    $logInput['company_id'] =  $company_id;
                    $logInput['created_by'] =  $logged_user->id;
                    $logInput['updated_by'] =  $logged_user->id;
                    LogActivity::addToActivityLogGuest($logInput);
                    if($logged_user->indiamart_integration!="Unassigned"){
                        $logInput['activity_type'] = 8;
                        $logInput['entry_type'] = "assigned";
                        $logInput['internal_remarks'] = "Assigned to " . $lastAssignedUser->name;
                        LogActivity::addToActivityLogGuest($logInput);

                        $noficationArr['customer_id'] = $ids;
                        $noficationArr['notification_type'] = "assign_to_you";
                        $noficationArr['device_key'] = $lastAssignedUser->device_key;
                        $noficationArr['mobile_device_key'] = $lastAssignedUser->mobile_device_key;
                        //$noficationArr['mobile_device_key'] = null;
                        $noficationArr['title'] = 'New Lead Assigned To You';
                        $noficationArr['body'] = $lead['SENDER_NAME'] . ' is assigned to you by ' . $lastAssignedUser->name;
                        $this->sendAssigntoUserNotification($noficationArr);
                    }

                    Lead_assign_users::where('company_id',$company_id)->where("user_id",$lastAssignedUser->user_id)->update(['assigned_list'=>1]);
                }

            }
        }
        /*if (!empty($request['RESPONSE'])) {
            $logged_user = User::select(["id", "company_id", "indiamart_integration", "name", "assigned_list"])->where('id', $user_id)->first();
            if ($logged_user) {
                $company_id = $logged_user->company_id ? $logged_user->company_id : $logged_user->id;
                $lead_id = CustomerLead::select('id')->where(function ($query) use ($company_id) {
                    $query->where('name', 'IndiaMART');
                    $query->where('company_id', $company_id);
                    $query->where('status', '=', 0);
                })->first();

                if (empty($lead_id)) {
                    $category_array = [
                        'name' => 'IndiaMart',
                        'user_id' => $logged_user->id,
                        'company_id' => $company_id
                    ];
                    $customerLeadId = CustomerLead::create($category_array);
                    $customer_lead_id = $customerLeadId['id'];
                } else {
                    $customer_lead_id = $lead_id['id'];
                }

                $last_user = 0;
                foreach ($request['RESPONSE'] as $key => $lead) {
                    $lastAssignedUser = Lead_assign_users::join("users", 'lead_assign_users.user_id', '=', 'users.id')->where('lead_assign_users.company_id', $company_id)->where('lead_assign_users.assigned_list', 0)->select('lead_assign_users.user_id', 'users.name', 'lead_assign_users.company_id', 'lead_assign_users.id', 'lead_assign_users.assigned_list', 'users.device_key', 'users.mobile_device_key')->orderBy('lead_assign_users.assigned_list', 'ASC')->orderBy('lead_assign_users.id', 'ASC')->first();

                    if (!$lastAssignedUser) {
                        Lead_assign_users::where('company_id', $company_id)->update(['assigned_list' => 0]);
                        $lastAssignedUser = Lead_assign_users::join("users", 'lead_assign_users.user_id', '=', 'users.id')->where('lead_assign_users.company_id', $company_id)->where('lead_assign_users.assigned_list', 0)->select('lead_assign_users.user_id', 'users.name', 'lead_assign_users.company_id', 'lead_assign_users.id', 'lead_assign_users.assigned_list', 'users.device_key', 'users.mobile_device_key')->orderBy('lead_assign_users.assigned_list', 'ASC')->orderBy('lead_assign_users.id', 'ASC')->first();
                    }


                    $assignedUserId = $logged_user->id;
                    $assignedUserName = $logged_user->name;

                    if (!empty($lead['SENDER_COMPANY'])) {
                        $customer_type = "Business";
                    } else {
                        $customer_type = "Individual";
                    }
                    $sender_mobile = explode("-", $lead['SENDER_MOBILE']);
                    $countryCode = $sender_mobile[0];
                    $phone_no = $sender_mobile[1];
                    $logInput['internal_remarks'] = "Lead added from IndiaMart";
                    $lead_data = [
                        'assigned_to_user' => $logged_user->indiamart_integration == "Unassigned" ? '' : $lastAssignedUser->user_id,
                        'user_id' => $logged_user->id,
                        'company_id' => $company_id,
                        'name' => $lead['SENDER_NAME'],
                        'email' => $lead['SENDER_EMAIL'],
                        'phone_no' => $phone_no,
                        'pincode' => $lead['SENDER_PINCODE'],
                        'address' => $lead['SENDER_ADDRESS'],
                        'description' => $lead['QUERY_MESSAGE'],
                        'new_lead_flag' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'customer_lead_id' => $customer_lead_id,
                        'customer_type' => $customer_type,
                        'country_code' => $countryCode
                    ];

                    if ($lead['SENDER_STATE'] != "") {
                        $state = State::select(['id', 'country_id'])->where(function ($query) use ($lead) {
                            $query->where('name', $lead['SENDER_STATE']);
                        })->first();
                        if (!empty($state)) {
                            $lead_data['state_id'] = $state['id'];
                        }
                    }
                    $lead_data['country_id'] = "101";
                    if ($lead['SENDER_COUNTRY_ISO'] != "") {
                        $country = Country::select(['id'])->where(function ($query) use ($lead) {
                            $query->where('sortname', $lead['SENDER_COUNTRY_ISO']);
                        })->first();
                        if (!empty($country)) {
                            $lead_data['country_id'] = $country['id'];
                        }
                    }

                    $customer = Customer::create($lead_data);
                    $ids = $customer->id;
                    $logInput['activity_type'] = 6;
                    $logInput['customer_id'] = $ids;
                    $logInput['user_id'] = $logged_user->id;
                    $logInput['company_id'] = $company_id;
                    $logInput['created_by'] = $logged_user->id;
                    $logInput['updated_by'] = $logged_user->id;
                    LogActivity::addToActivityLogGuest($logInput);
                    if ($logged_user->indiamart_integration != "Unassigned") {
                        $logInput['activity_type'] = 8;
                        $logInput['entry_type'] = "assigned";
                        $logInput['internal_remarks'] = "Assigned to " . $lastAssignedUser->name;
                        LogActivity::addToActivityLogGuest($logInput);

                        $noficationArr['customer_id'] = $ids;
                        $noficationArr['notification_type'] = "assign_to_you";
                        $noficationArr['device_key'] = $lastAssignedUser->device_key;
                        $noficationArr['mobile_device_key'] = $lastAssignedUser->mobile_device_key;
                        //$noficationArr['mobile_device_key'] = null;
                        $noficationArr['title'] = 'New Lead Assigned To You';
                        $noficationArr['body'] = $lead['SENDER_NAME'] . ' is assigned to you by ' . $lastAssignedUser->name;
                        $this->sendAssigntoUserNotification($noficationArr);
                    }

                    Lead_assign_users::where('company_id', $company_id)->where("user_id", $lastAssignedUser->user_id)->update(['assigned_list' => 1]);
                }

            }
        }*/
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

    public function update_users_roundrobin(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $company_id = ($user->company_id) ? $user->company_id : $user->id;
            $input = $request->all();
            ## Read value
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length"); // Rows display per page

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            // Fetch records
            $name = $request->get('name');
            $status = $request->get('status');
            // Total records

            $totalRecords = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orwhere('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('email', 'like', '%' . $search_arr . '%');
                        $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                        $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                    }
                })
                ->where('invite_status', 1)
                ->select('count(u1.id) as allcount')
                ->count();

            $totalRecordswithFilter = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orwhere('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('email', 'like', '%' . $search_arr . '%');
                        $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                        $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                    }
                })
                ->where('invite_status', 1)
                ->select('count(u1.id) as allcount')
                ->count();

            $records = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orwhere('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('email', 'like', '%' . $search_arr . '%');
                        $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                        $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                    }
                })
                ->where('invite_status', 1)
                ->select(['id', 'name', 'mobile_no', 'email', 'role_name', 'invite_status', 'company_id'])
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();
            $data = array();
            $i = 0;
            $assigned_users = Lead_assign_users::where('company_id', $company_id)->get()->toArray();
            $assigned_users = array_column($assigned_users, 'user_id');
            foreach ($records as $record) {
                $is_checked = "";
                // echo $record->id;
                // echo in_array($record->id, $assigned_users);
                // print_r($assigned_users);exit;
                if (in_array($record->id, $assigned_users)) {
                    $is_checked = "checked";
                }
                $id = $record->id;
                $role_name = $record->role_name;
                $name = $record->name;
                $email = $record->email;
                $mobile_no = $record->mobile_no;
                $status = $record->invite_status;
                $company_id = $record->company_id;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "role_name" => $role_name,
                    "name" => $name,
                    "email" => $email,
                    "mobile_no" => $mobile_no,
                    "status" => $status,
                    "action" => $id,
                    "is_checked" => $is_checked,
                    "company_id" => $company_id,
                );
            }
            //            $totalRecords = 0;
            //            $totalRecordswithFilter = 0;

            $response = array(
                "draw" => intval($draw),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalRecordswithFilter,
                "data" => $data
            );
            return json_encode($response);
        }
        $user = Auth::user();
        $id = isset($user->company_id) ? $user->company_id : $user->id;
        $userCount = User::where('company_id', $id)->count();
        $plan = PlanHistory::where([['user_id', $id], ['status', 1]])->first();
        return view('integration-user-list')->with(['userCount' => $userCount, 'plan' => $plan]);
    }

    public function update_users_roundrobin_facebook(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $company_id = ($user->company_id) ? $user->company_id : $user->id;
            $input = $request->all();
            ## Read value
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length"); // Rows display per page

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            // Fetch records
            $name = $request->get('name');
            $status = $request->get('status');
            // Total records

            $totalRecords = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orwhere('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('email', 'like', '%' . $search_arr . '%');
                        $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                        $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                    }
                })
                ->where('invite_status', 1)
                ->select('count(u1.id) as allcount')
                ->count();

            $totalRecordswithFilter = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orwhere('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('email', 'like', '%' . $search_arr . '%');
                        $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                        $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                    }
                })
                ->where('invite_status', 1)
                ->select('count(u1.id) as allcount')
                ->count();

            $records = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orwhere('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('email', 'like', '%' . $search_arr . '%');
                        $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                        $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                    }
                })
                ->where('invite_status', 1)
                ->select(['id', 'name', 'mobile_no', 'email', 'role_name', 'invite_status', 'company_id'])
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();
            $data = array();
            $i = 0;
            $assigned_users = LeadAssignFbUser::where('company_id', $company_id)->get()->toArray();
            $assigned_users = array_column($assigned_users, 'user_id');
            foreach ($records as $record) {
                $is_checked = "";
                // echo $record->id;
                // echo in_array($record->id, $assigned_users);
                // print_r($assigned_users);exit;
                if (in_array($record->id, $assigned_users)) {
                    $is_checked = "checked";
                }
                $id = $record->id;
                $role_name = $record->role_name;
                $name = $record->name;
                $email = $record->email;
                $mobile_no = $record->mobile_no;
                $status = $record->invite_status;
                $company_id = $record->company_id;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "role_name" => $role_name,
                    "name" => $name,
                    "email" => $email,
                    "mobile_no" => $mobile_no,
                    "status" => $status,
                    "action" => $id,
                    "is_checked" => $is_checked,
                    "company_id" => $company_id,
                );
            }
            //            $totalRecords = 0;
            //            $totalRecordswithFilter = 0;

            $response = array(
                "draw" => intval($draw),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalRecordswithFilter,
                "data" => $data
            );
            return json_encode($response);
        }
    }

    public function update_users_roundrobin_status(Request $request)
    {
        $input = $request->all();
        $user_ids = explode(",", $input['id']);

        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $customer = Lead_assign_users::where("company_id", $company_id)->delete();
        foreach ($user_ids as $key => $value) {
            $user_data = [
                "company_id" => $company_id,
                "user_id" => $value,
                "assigned_list" => 0
            ];
//            if($input['status']==0){
            $customer = Lead_assign_users::updateOrCreate([
                "company_id" => $company_id,
                "user_id" => $value,
            ], [
                "user_id" => $value,
            ]);
            /*}else{
                $customer = Lead_assign_users::where("company_id", $company_id)->delete();
            }*/
        }

        return response()->json(['success' => 'Successfully saved!'], 201);
    }

    public function disconnectedIndiamart(Request $request)
    {
        $input = $request->all();
//        $user = Auth::user();
        Indiamart_api_tokens::where("indiamart_token", $input['indiamart_token'])->delete();

        return response()->json(['success' => 'Successfully saved!'], 201);
    }

    public function update_users_roundrobin_facebook_status(Request $request)
    {
        $input = $request->all();
        $user_ids = explode(",", $input['id']);

        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $customer = LeadAssignFbUser::where("company_id", $company_id)->delete();
        foreach ($user_ids as $key => $value) {
            $user_data = [
                "company_id" => $company_id,
                "user_id" => $value,
                "assigned_list" => 0
            ];
//            if($input['status']==0){
            $customer = LeadAssignFbUser::updateOrCreate([
                "company_id" => $company_id,
                "user_id" => $value,
            ], [
                "user_id" => $value,
            ]);
            /*}else{
                $customer = Lead_assign_users::where("company_id", $company_id)->delete();
            }*/
        }

        return response()->json(['success' => 'Successfully saved!'], 201);
    }

    public function tradeindia_lead_verify(Request $request)
    {
        $data1 = $request->all();
        $currentTimeStamp = Carbon::now()->format('Y-m-d');


        // URL of the endpoint
        $url = 'https://www.tradeindia.com/utils/my_inquiry.html';

        // Data to be sent in the request
        $data = array(
            'userid' => $data1['tradeindia_user_id'],
            'profile_id' => $data1['tradeindia_profile_id'],
            'key' => $data1['tradeindia_token'],
            'from_date' => $currentTimeStamp,
            'to_date' => $currentTimeStamp
        );

        try {
            // Send GET request
            $response = Http::get($url, $data);
            $responseArray = json_decode($response->body(), true);


            if($responseArray=='Sorry! You are not authorized to view this.'){
                return response()->json(['errors' => 'Sorry! You are not authorized to view this.'], 400);
            }
            // Get the response body
            $responseArray = json_decode($response->body(), true);
            $value['user_id'] = $this->company_id;
            $value['tradeindia_token'] = $data1['tradeindia_token'];
            $value['tradeindia_user_id'] = $data1['tradeindia_user_id'];
            $value['tradeindia_profile_id'] = $data1['tradeindia_profile_id'];
            $customer = TradeindiaApiToken::updateOrCreate($value);
            return response()->json(['success' => 'Successfully Integrated!'], 201);
            // Output the response
//            return response()->json($responseArray);
        } catch (\Exception $e) {
            // Handle any exceptions or errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function get_tradeindia_apitoken()
    {

        $data = TradeindiaApiToken::select(["id", "tradeindia_token","tradeindia_user_id", "tradeindia_profile_id"])->where('user_id', $this->company_id)->first();

        return response()->json(['success' => 'Successfully saved!', 'data' => $data], 201);
    }

    public function disconnectedTradeindia(Request $request)
    {
        $input = $request->all();
//        $user = Auth::user();
        TradeindiaApiToken::where("tradeindia_token", $input['tradeindia_token'])->delete();

        return response()->json(['success' => 'Successfully saved!'], 201);
    }

    public function update_users_roundrobin_tradeindia(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $company_id = ($user->company_id) ? $user->company_id : $user->id;
            $input = $request->all();
            ## Read value
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length"); // Rows display per page

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            // Fetch records
            $name = $request->get('name');
            $status = $request->get('status');
            // Total records

            $totalRecords = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orwhere('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('email', 'like', '%' . $search_arr . '%');
                        $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                        $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                    }
                })
                ->where('invite_status', 1)
                ->select('count(u1.id) as allcount')
                ->count();

            $totalRecordswithFilter = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orwhere('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('email', 'like', '%' . $search_arr . '%');
                        $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                        $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                    }
                })
                ->where('invite_status', 1)
                ->select('count(u1.id) as allcount')
                ->count();

            $records = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orwhere('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('email', 'like', '%' . $search_arr . '%');
                        $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                        $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                    }
                })
                ->where('invite_status', 1)
                ->select(['id', 'name', 'mobile_no', 'email', 'role_name', 'invite_status', 'company_id'])
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();
            $data = array();
            $i = 0;
            $assigned_users = LeadAssignTiUser::where('company_id', $company_id)->get()->toArray();
            $assigned_users = array_column($assigned_users, 'user_id');
            foreach ($records as $record) {
                $is_checked = "";
                // echo $record->id;
                // echo in_array($record->id, $assigned_users);
                // print_r($assigned_users);exit;
                if (in_array($record->id, $assigned_users)) {
                    $is_checked = "checked";
                }
                $id = $record->id;
                $role_name = $record->role_name;
                $name = $record->name;
                $email = $record->email;
                $mobile_no = $record->mobile_no;
                $status = $record->invite_status;
                $company_id = $record->company_id;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "role_name" => $role_name,
                    "name" => $name,
                    "email" => $email,
                    "mobile_no" => $mobile_no,
                    "status" => $status,
                    "action" => $id,
                    "is_checked" => $is_checked,
                    "company_id" => $company_id,
                );
            }
            //            $totalRecords = 0;
            //            $totalRecordswithFilter = 0;

            $response = array(
                "draw" => intval($draw),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalRecordswithFilter,
                "data" => $data
            );
            return json_encode($response);
        }
        $user = Auth::user();
        $id = isset($user->company_id) ? $user->company_id : $user->id;
        $userCount = User::where('company_id', $id)->count();
        $plan = PlanHistory::where([['user_id', $id], ['status', 1]])->first();
        return view('integration-user-list')->with(['userCount' => $userCount, 'plan' => $plan]);
    }

    public function update_users_roundrobin_tradeindia_status(Request $request)
    {
        $input = $request->all();
        $user_ids = explode(",", $input['id']);

        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $customer = LeadAssignTiUser::where("company_id", $company_id)->delete();
        foreach ($user_ids as $key => $value) {
            $user_data = [
                "company_id" => $company_id,
                "user_id" => $value,
                "assigned_list" => 0
            ];
//            if($input['status']==0){
            $customer = LeadAssignTiUser::updateOrCreate([
                "company_id" => $company_id,
                "user_id" => $value,
            ], [
                "user_id" => $value,
            ]);
            /*}else{
                $customer = LeadAssignTiUser::where("company_id", $company_id)->delete();
            }*/
        }

        return response()->json(['success' => 'Successfully saved!'], 201);
    }

    public function whatsapp_auth_verify(Request $request)
    {
        $data1 = $request->all();

        try {
            $value['whatsapp_auth_token'] = $data1['whatsapp_auth_token'];
            $value['whatsapp_open_chat_token'] = $data1['whatsapp_open_chat_token'];

            $customer = User::find($this->company_id)->update($value);
            return response()->json(['success' => 'Successfully Integrated!'], 201);
            // Output the response
//            return response()->json($responseArray);
        } catch (\Exception $e) {
            // Handle any exceptions or errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}

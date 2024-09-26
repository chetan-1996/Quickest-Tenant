<?php

namespace App\Http\Controllers\API\V7;

use App\Http\Controllers\API\V7\BaseController as BaseController;
use App\Models\Country;
use App\Models\LeadStage;
use App\Models\User;
use App\Models\Tenant;
use App\Models\VerificationCode;
use App\Models\UserPermission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Crypt, DB, Hash, Storage};
use Illuminate\Http\RedirectResponse;
use Validator;
use Auth;
use App\Models\admin\Plans;
use App\Models\Estimate;
use App\Models\EstimateAutoNumber;
use App\Models\PlanHistory;
use App\Models\ProposalTemplates;
use App\Models\Unit;
use Image;
use LogActivity;

class AuthController extends BaseController
{
    public function __construct()
    {
        $this->username = $this->findUsername();
    }
    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenants',
            'mobile_no' => 'required',
            'country_id' => 'required',
            'company_category' => 'required',
            // 'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 200);
            //            return response()->json($validator->errors());
        }

        $start_date = date('Y-m-d H:i:s');
        $from_date = date('Y-m-d H:i:s', strtotime("+7 day", strtotime($start_date)));
        $plan = Plans::where('isDefault', 1)->first();

        $cleanCompanyName =str_replace(' ', '', preg_replace('/[^a-zA-Z0-9\s]/ ', '', $input['company_name']));
        $baseDomain = Str::slug($cleanCompanyName);
        $generateDomainName = $this->generateDomainName($baseDomain);
        $tenant = Tenant::query()->create([
            'is_owner' => $request->is_owner,
            'name' => $request->name,
            'email' => $request->email,
            'company_name' => $request->company_name,
            'plan_start_date' => $start_date,
            'plan_end_date' => $from_date,
            'plan_id' => $plan->id,
            'status' => 'New',
            'invite_status' => 1,
            'mobile_no' => $request->mobile_no,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'company_category' => $request->company_category,
            'domain' => $generateDomainName,
            'password' => Hash::make($cleanCompanyName.'@12345678'),
            'is_accepted_terms_condition' => $request->is_accepted_terms_condition,
        ]);

        PlanHistory::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            // 'user_limit' => $plan->users_limit - 1,
            'user_limit' => $plan->users_limit,
            'estimate_limit' => $plan->estimate_limit,
            'status' => 1,
            'start_date' => $start_date,
            'end_date' => $from_date
        ]);
        $otp = rand(100000, 999999);

        if (User::where('email', '=', $request->email)->update(['otp' => $otp])) {
            $mail_details = [
                'subject' => 'OTP for your Quickest sign-in',
                'body' => $otp
            ];
            \Mail::to($request->email)->send(new \App\Mail\SendOtpMail($mail_details));
            return $this->sendResponse([], 'OTP sent successfully');
            //            return response(["status" => 200, "message" => "OTP sent successfully"]);
        } else {
            return $this->sendError('Validation error', ['error' => "Invalid"], 200);
        }
        //        Auth::login($user, true);
        //        $user->sendEmailVerificationNotification();
        //        $token = $user->createToken('auth_token')->plainTextToken;
        //
        //        return response()->json(['data' => $user, 'access_token' => $token, 'token_type' => 'Bearer',]);
    }

    public function findUsername()
    {
        $login = request()->input('email');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile_no';

        request()->merge([$fieldType => $login]);

        return $fieldType;
    }

    public function login(Request $request)
    {
        if (!Auth::attempt([$this->username => $request->email, 'password' => $request->password])) {
            return $this->sendError('Unauthorized', ['error' => 'Unauthorised'], 401);
        }
        $user = Auth::user();
        //        $user = User::where('email', $request['email'])->firstOrFail();

        /* $now = Carbon::now();
        $expired_plan = Carbon::createFromFormat('Y-m-d H:i:s', $user->plan_end_date)->format('Y-m-d H:i:s');
        if (strtotime($now) <= strtotime($expired_plan)) {
            return $this->sendError('Expired', ['error' => 'Expired']);
        }*/

        $token = $user->createToken('auth_token')->plainTextToken;

        $abc = $this->multilevel_categories($user->id);
        $array = $this->nestedToSingle($abc);
        if (empty($array)) {
            $array[] = $user->id;
        }

        if ($user->profile_icon == null) {
            $user->profile_icon = null;
        } else {
            // $user->profile_icon = Storage::url($user->profile_icon);
            $user->profile_icon = Storage::disk('s3')->temporaryUrl($user->profile_icon,Carbon::now()->addMinutes(20));
        }

        if ($user->company_category == 0) {
            $user->company_category = 0;
            if ($user->company_id) {
                $userData = DB::table('users')->where('id', '=', $user->company_id)->select('company_category')->first();
                $user->company_category = $userData->company_category;
            }
        }
        $roleData = DB::table('roles')->where('id', '=', $user->role_id)->select('name as role_name')->first();
        $role_name = "Founder";
        if ($roleData)
            $role_name = $roleData->role_name;
        $user->role_name = $role_name;
        $data['user'] = $user;
        $data['assign_user'] = implode(',', $array);
        $data['access_token'] = $token;
        $data['token_type'] = 'Bearer';
        return $this->sendResponse($data, 'User signed in');
    }

    public function multilevel_categories($parent_id = 0)
    {
        $query = DB::table('users')->select('id')->where('user_id', $parent_id)->get();

        $catData = [];
        if ($query->count() > 0) {
            foreach ($query as $row) {
                $catData[] = [
                    'id' => $row->id,
                    'nested_categories' => $this->multilevel_categories($row->id)
                ];
            }
            return $catData;
        } else {
            return $catData = [];
        }
    }

    public function nestedToSingle(array $array)
    {
        $singleDimArray = [];

        foreach ($array as $item) {

            if (is_array($item)) {
                $singleDimArray = array_merge($singleDimArray, $this->nestedToSingle($item));
            } else {
                $singleDimArray[] = $item;
            }
        }

        return $singleDimArray;
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        if ($user->profile_icon == null) {
            $user->profile_icon = null;
        } else {
            // $user->profile_icon = Storage::url($user->profile_icon);
            $user->profile_icon = Storage::disk('s3')->temporaryUrl($user->profile_icon,Carbon::now()->addMinutes(20));
        }

        if ($user->company_category == 0) {
            $user->company_category = 0;
            if ($user->company_id) {
                $userData = DB::table('users')->where('id', '=', $user->company_id)->select('company_category')->first();
                $user->company_category = $userData->company_category;
            }
        }

        /*$roleData = DB::table('roles')->where('id', '=', $user->role_id)->select('name as role_name')->first();
        $role_name = "Founder";
        if ($roleData)
            $role_name = $roleData->role_name;
        $user->role_name = $role_name;*/
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $company_data = DB::table('users')->where('id', '=', $company_id)->select('*')->first();
        $country_data = DB::table('countries')->where('id', '=', $company_data->country_id)->select('*')->first();
        $user->pemmission = \App\Helpers\PermissionCheck::check_permission('role-list');
        $user->country_data = $country_data;
        return $user;
    }

    // method for user logout and delete token
    public function logout()
    {
        $user = Auth::user();
        User::find($user->id)->update(["mobile_device_key" => '']);
        auth()->user()->tokens()->delete();
        return $this->sendResponse(["msg" => 'You have successfully logged out'], 'You have successfully logged out');
        //        return [
        //            'message' => 'You have successfully logged out and the token was successfully deleted'
        //        ];
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
            //            return response()->json($validator->errors());
        }
        
        $user = Tenant::where('email', $request->email)->first();
        
        if (!$user) {
            return $this->sendError('This Email Address is not exist...', ['error' => 'This Email Address is not exist...'], 200);
        }
        if ($user->invite_status == 0 || $user->invite_status == 2) {
            return $this->sendError('Inactivated', ['error' => 'Your account is deactivated , please contact your admin.'], 200);
        }
        $companyId = $user->company_id ? $user->company_id : $user->id;
        $companyData = Tenant::where('company_id', $companyId)->get();
        // $planData = PlanHistory::where([['user_id', $companyId], ['status', 1]])->first();
        // if (isset($user->company_id) && $companyData) {
        //     // $user_limit = $planData->user_limit - 1;
        //     $user_limit = $planData->user_limit;
        //     foreach ($companyData as $key => $company) {
        //         if ($user->id == $company->id && $key >= $user_limit) {
        //             return $this->sendError('Inactivated', ['error' => 'Your account deactivate please upgrade your plan...'], 200);
        //         }
        //     }
        // }
        $db_name = 'quickest_'.$user->domain;
        config([
            'database.connections.tenant.database' => $db_name,
        ]);
        DB::setDefaultConnection('tenant');

        $tuser = User::where('email', $request->email)->first();
        $verificationCode = VerificationCode::where('user_id', $tuser->id)->latest()->first();
        $now = Carbon::now();

        if ($verificationCode && $now->isBefore($verificationCode->expire_at)) {
            VerificationCode::where('id', $verificationCode->id)->update([
                'otp' => $verificationCode->otp
            ]);
            $updatedverificationCode = VerificationCode::where('user_id', $tuser->id)->latest()->first();
            return $updatedverificationCode;
        }

        // Create a New OTP

        if($request->email == 'demo@quickestimate.co'){
            $tmpOtp = "123456";
        }else{
            $tmpOtp = rand(123456, 999999);
        }
        VerificationCode::create([
            'user_id' => $tuser->id,
            'otp' => $tmpOtp,
            'expire_at' => Carbon::now()->addMinutes(20)
        ]);

        DB::disconnect('tenant'); //connection close

        if ($user) {
            $mail_details = [
                'subject' => 'OTP for your Quickest sign-in',
                'body' => $tmpOtp
            ];

            \Mail::to($request->email)->send(new \App\Mail\SendOtpMail($mail_details));
            return response(["status" => 200, 'message' => 'OTP sent successfully', 'domain' => $user->domain]);
        } else {
            return response(["status" => 400, 'message' => 'Invalid']);
        }
    }

    public function verifyOtp(Request $request)
    {
        //DB::enableQueryLog();
        $user = User::where('email', $request->email)->first();
        //$queries = DB::getQueryLog();dd($queries);

        if (!$user) {
            return $this->sendError('This Email Address is not exist...', ['error' => 'This Email Address is not exist...'], 200);
        }
        if($request->email == 'demo@quickestimate.co'){
            $verificationCode = verificationCode::where([['user_id', '=', $user->id], ['otp', '=', "123456"]])->first();
        }else{
            $verificationCode = verificationCode::where([['user_id', '=', $user->id], ['otp', '=', $request->otp]])->first();
        }
       
        if ($verificationCode) {
            $datetime = date('Y-m-d H:i:s');
            User::where('email', '=', $request->email)->update(['email_verified_at' => $datetime]);
            $user->email_verified_at = $datetime;
            auth()->login($user, true);
            //verificationCode::where('user_id', '=', $user->id)->update(['otp' => null]);
            //            $accessToken = auth()->user()->createToken('authToken')->accessToken;
            $token = $user->createToken('auth_token')->plainTextToken;

            $abc = $this->multilevel_categories($user->id);
            $array = $this->nestedToSingle($abc);
            if (empty($array)) {
                $array[] = $user->id;
            }

            if ($user->profile_icon == null) {
                $user->profile_icon = null;
            } else {
                // $user->profile_icon = Storage::url($user->profile_icon);
                $user->profile_icon = Storage::disk('s3')->temporaryUrl($user->profile_icon,Carbon::now()->addMinutes(20));
            }

            if ($user->company_category == 0) {
                $user->company_category = 0;
                if ($user->company_id) {
                    $userData = DB::table('users')->where('id', '=', $user->company_id)->select('company_category')->first();
                    $user->company_category = $userData->company_category;
                }
            }

           /* $roleData = DB::table('roles')->where('id', '=', $user->role_id)->select('name as role_name')->first();
            $role_name = "Founder";
            if ($roleData)
                $role_name = $roleData->role_name;
            $user->role_name = $role_name;*/
            $data['user'] = $user;
            $data['assign_user'] = implode(',', $array);
            $data['access_token'] = $token;
            $data['token_type'] = 'Bearer';
            $data['pemmission'] = \App\Helpers\PermissionCheck::check_permission('role-list');
            return $this->sendResponse($data, 'User signed in');
            //            return response(["status" => 200, "message" => "Success", 'user' => auth()->user(), 'access_token' => $accessToken]);
        } else {
            return response(["status" => 401, 'message' => 'Invalid']);
        }
    }

    public function verifyOtpRegister(Request $request)
    {
        $user = User::where([['email', '=', $request->email], ['otp', '=', $request->otp]])->first();
        if ($user) {
            User::where('email', '=', $request->email)->update(['otp' => null, 'status' => 'New', 'email_verified_at' => date('Y-m-d H:i:s')]);
            return $this->sendResponse([], 'Successfully OTP verified');
        } else {
            return $this->sendError('Invalid OTP', ['error' => "Invalid OTP"], 400);
        }
    }

    public function isUserVerified(Request $request)
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return $this->sendResponse([], 'User verified.');
        } else {
            return $this->sendError('User dont verified.', ['error' => "User dont verified."], 200);
        }
    }

    public function isCheckUserStatus(Request $request)
    {
        if (Auth::user()->status == 'Approved') {
            return $this->sendResponse([['account_status' => 'Approved']], 'Your account is activated');
        }

        if (Auth::user()->status == 'New') {
            return $this->sendResponse([['account_status' => 'New']], 'Please fill below field');
        }

        if (Auth::user()->status == 'Pending') {
            return $this->sendResponse([['account_status' => 'Pending']], 'You have registered successfully. Your account will we actived soon.');
        }

        if (Auth::user()->status == 'Rejected') {
            return $this->sendResponse([['account_status' => 'Rejected']], 'Your account is rejected.');
        }
    }

    public function companyProfileUpdate(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();
        $validator = \Illuminate\Support\Facades\Validator::make($input, [
            'company_name' => 'required',
            'mobile_no' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            //            'city_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        if ($request->hasFile('profile_icon')) {
            $request->validate([
                'profile_icon' => 'image|mimes:jpeg,png,jpg|max:1024',
            ]);
            /*if (Storage::exists($user->profile_icon)) {
                Storage::delete($user->profile_icon);
            }

            $path = $request->file('profile_icon')->store('public/profile');
            $input['profile_icon'] = $path;*/
            $tmp_company_id = $user->id;

            if(Storage::exists($user->profile_icon))
            {
                Storage::disk('s3')->delete($user->profile_icon);
            }
            $path = Storage::disk('s3')->put('public/'.$tmp_company_id.'/profile', $request->profile_icon,'public');
            $input['profile_icon'] = 'public/'.$tmp_company_id.'/profile/'.basename(Storage::disk('s3')->url($path));
        }


        $input['status'] = 'Approved';
        User::find($user->id)->update($input);
        $tmpId = $user->id;
        if ($input['status'] == 'Approved') {

            if (!EstimateAutoNumber::where('company_id', $tmpId)->select('id')->first()) {
                $data_ins['estimate_prefix'] = 'EST-';
                $data_ins['estimate_next_no'] = '001';
                $data_ins['company_id'] = $tmpId;

                $unit = EstimateAutoNumber::create($data_ins);
            }
            $unitArr = [
                ['name' => 'Site', 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Kw', 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Nos', 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Kg', 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Meter', 'user_id' => $tmpId, 'company_id' => $tmpId],
            ];
            DB::table('units')->insert($unitArr);

            $lostReasonArr = [
                ['name' => 'Costly', 'user_id' => $tmpId, 'company_id' => $tmpId, "priority" => 0],
                ['name' => 'Duplicate Lead', 'user_id' => $tmpId, 'company_id' => $tmpId, "priority" => 0],
                ['name' => 'Finalize other solution', 'user_id' => $tmpId, 'company_id' => $tmpId, "priority" => 0],
                ['name' => 'No budget', 'user_id' => $tmpId, 'company_id' => $tmpId, "priority" => 0],
                ['name' => 'No Need', 'user_id' => $tmpId, 'company_id' => $tmpId, "priority" => 0],
                ['name' => 'Only Info. required', 'user_id' => $tmpId, 'company_id' => $tmpId, "priority" => 0],
                ['name' => 'Require specific brand only', 'user_id' => $tmpId, 'company_id' => $tmpId, "priority" => 0],
                ['name' => 'Others', 'user_id' => $tmpId, 'company_id' => $tmpId, "priority" => 1]
            ];
            DB::table('lost_reasons')->insert($lostReasonArr);

            $dashboardSettingArr = [
                ['permission_id' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId, 'is_primary' =>0],
                ['permission_id' => 2, 'user_id' => $tmpId, 'company_id' => $tmpId, 'is_primary' =>1],
//                ['permission_id' => 3, 'user_id' => $tmpId, 'company_id' => $tmpId, 'is_primary' =>0],
                ['permission_id' => 4, 'user_id' => $tmpId, 'company_id' => $tmpId, 'is_primary' =>1],
                ['permission_id' => 5, 'user_id' => $tmpId, 'company_id' => $tmpId, 'is_primary' =>0],
                ['permission_id' => 6, 'user_id' => $tmpId, 'company_id' => $tmpId, 'is_primary' =>1],
            ];
            DB::table('dashboard_settings')->insert($dashboardSettingArr);

            $leadStageArr = [
                ['name' => 'New Lead', 'color_code' => '#006398','is_default'=>1,'priority'=>1,'is_delete'=>1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Inprocess', 'color_code' => '#fdac64','is_default'=>0,'priority'=>2,'is_delete'=>0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Qualified', 'color_code' => '#c47933','is_default'=>0,'priority'=>3,'is_delete'=>0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Estimate Sent', 'color_code' => '#f678c3','is_default'=>4,'priority'=>6,'is_delete'=>1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Lead Won', 'color_code' => '#13a764','is_default'=>2,'priority'=>10,'is_delete'=>1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Lead Lost', 'color_code' => '#fa4e64','is_default'=>6,'priority'=>8,'is_delete'=>1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'On Hold', 'color_code' => '#ab408b','is_default'=>0,'priority'=>9,'is_delete'=>0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Site visit schedule', 'color_code' => '#006398','is_default'=>0,'priority'=>4,'is_delete'=>0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Site visit done', 'color_code' => '#fdac64','is_default'=>0,'priority'=>5,'is_delete'=>0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Negotiation', 'color_code' => '#c47933','is_default'=>0,'priority'=>7,'is_delete'=>0, 'user_id' => $tmpId, 'company_id' => $tmpId],
            ];
            DB::table('lead_stages')->insert($leadStageArr);

            $lead_stage_data = LeadStage::where('company_id',$tmpId)->where('is_default',1)->select('name','id')->first();

            $country_data = Country::where('id',$input['country_id'])->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->first();

            $customersArr = [
                ['customer_type' => 'Individual', 'name' => 'Quickest Support', 'phone_no' => '9724294153', 'email' => 'contact@quickestimate.co', 'country_code' => '+'.$input['country_code'], 'user_id' => $tmpId, 'company_id' => $tmpId, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'assigned_to_user' => $tmpId, 'country_id' => $input['country_id'], 'state_id' => 0, 'city_name' => 'Surat', 'address' => '97 Dadiseth Agiary Lane Kalbadevi','currency_name' => $country_data->currency_code,'whatsapp_country_code' => '+'.$input['country_code'],"whatsapp_no" =>'9724294153','phone_no_country_id'=>$input['country_id'],'whatsapp_no_country_id'=>$input['country_id'],'currency_name_country_id'=>$input['country_id'],'lead_stage_id' =>$lead_stage_data->id]
            ];
            DB::table('customers')->insert($customersArr);
            $customersInsertId = DB::getPdo()->lastInsertId();
            if($customersInsertId){
                $logInput['internal_remarks'] = "Lead added by manually";
                $logInput['new_lead_flag'] = 1;
                $logInput['activity_type'] = 6;
                $logInput['assigned_to'] = $tmpId;
                $logInput['customer_id'] = $customersInsertId;
                $logInput['entry_type'] = "leads";

                $logInput['user_id'] = $tmpId;
                $logInput['company_id'] = $tmpId;
                $logInput['created_by'] = $tmpId;
                $logInput['updated_by'] = $tmpId;
                LogActivity::addToActivityLog($logInput);

                $logInput['activity_type'] = 8;
                $logInput['entry_type'] = "assigned";
                $logInput['follow_up_datetime'] = "0000-00-00 00:00:00";
                $logInput['internal_remarks'] = "Assigned to " . $user->name;
                LogActivity::addToActivityLog($logInput);
            }

            $taxArr = [
                ['name' => 0.00, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 5.00, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 12.00, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 13.8, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 18.00, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 28.00, 'user_id' => $tmpId, 'company_id' => $tmpId]
            ];
            DB::table('taxs')->insert($taxArr);

            $catArr = [
                ['name' => 'B2B', 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'B2C', 'user_id' => $tmpId, 'company_id' => $tmpId]
            ];
            DB::table('customer_categories')->insert($catArr);

            $leadArr = [
                ['name' => 'Social Media', 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Reference', 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['name' => 'Physical Marketing', 'user_id' => $tmpId, 'company_id' => $tmpId]
            ];
            DB::table('customer_leads')->insert($leadArr);

            $units = Unit::where([["company_id", "=", $tmpId], ["name", "=", "Kw"]])->orderBy('id', 'ASC')->select("id")->first();


            $userDatas = DB::table('users')->where('id', '=', $tmpId)->select(['company_category', 'name', 'email'])->first();
            /*if ($userDatas->company_category == 1) {
                $path_one = env('APP_URL') . "sample/testimonial/t-1.png";
                $filename_one = date('YmdHis') . "106" . ".png";
                Image::make($path_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_one));

                $path_two = env('APP_URL') . "sample/testimonial/t-2.png";
                $filename_two = date('YmdHis') . "107" . ".png";
                Image::make($path_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_two));

                $path_three = env('APP_URL') . "sample/testimonial/t-3.png";
                $filename_three = date('YmdHis') . "108" . ".png";
                Image::make($path_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_three));

                $testimonialArr = [
                    [
                        'name' => 'Residential Testimonial',
                        'client_name_one' => 'Rahulbhai patel',
                        'client_name_two' => 'Payalben hirpara',
                        'client_name_three' => 'Kiranbhai prajapati',
                        'description_one' => 'I recently installed solar on my rooftop. Thank You team for neat and clean Installation on my rooftop. Also, received my subsidy. Great Work team. Thanks',
                        'description_two' => 'When you have empty roof then why to pay for electricity bill? Thank You for end to end guidance. Your staff is very professional and friendly. Thanks for making my roof solarize! Superb Work by Team.',
                        'description_three' => 'One of the best decision of my life to go solar! I really appreciate your product quality and workmanship. In last 6 month, my plant has generated more than 2000 Units and counting. I strongly recommend everyone to go solar as soon as possible. Thank You',
                        'rating_one' => 5,
                        'rating_two' => 5,
                        'rating_three' => 5,
                        'image_one' => 'public/uploads/thumbnail/' . $filename_one,
                        'image_two' => 'public/uploads/thumbnail/' . $filename_two,
                        'image_three' => 'public/uploads/thumbnail/' . $filename_three,
                        'status' => 0,
                        'is_default' => 1,
                        'user_id' => $tmpId,
                        'company_id' => $tmpId,
                    ],

                ];
                DB::table('testimonials')->insert($testimonialArr);
                $itemArr = [
                    ['name' => '3.015 KW on grid rooftop solar (URBAN 335W -09P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
                    - Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 87988, 'sales_flag' => 1, 'status' => 0, 'purchase_flag' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '3.35 KW on grid rooftop solar (URBAN 335W -10P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
                    - Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 100874, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '3.685 KW on grid rooftop solar (URBAN 335W -11P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (11 Qty)
                    - Inverter 3.6 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 61,575 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 114553, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '3.015 KW on grid rooftop solar (RURAL 335W 09P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (09 Qty)
                    - Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 56,117 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 91419, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '3.35 KW on grid rooftop solar (RURAL 335W -10P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
                    - Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 104686, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '3.685 KW on grid rooftop solar (RURAL 335W -11P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (11 Qty)
                    - Inverter 3.6 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 61,575 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 118747, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '4.02 KW on grid rooftop solar (335W -12P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (12 Qty)
                    - Inverter 4.2 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 65,493 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 126646, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '4.355 KW on grid rooftop solar (335W -13P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (13 Qty)
                    - Inverter 4.2 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 67,173 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 140979, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '4.69 KW on grid rooftop solar (335W -14P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (14 Qty)
                    - Inverter 5 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - TransportationG
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 71,744 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 152419, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '5.025 KW on grid rooftop solar (335W -15P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (15 Qty)
                    - Inverter 5 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 74,636 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 165538, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '5.36 KW on grid rooftop solar (335W -16P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (16 Qty)
                    - Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 77,995 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 172092, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '5.695 KW on grid rooftop solar (335W -17P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (17 Qty)
                    - Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 81,120 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 184597, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '6.03 KW on grid rooftop solar (335W -18P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (18 Qty)
                    - Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 83,966 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 197382, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '7.035 KW on grid rooftop solar (335W -21P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (21 Qty)
                    - Inverter 8 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 92,501 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 231735, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '8.04 KW on grid rooftop solar (335W -24P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (24 Qty)
                    - Inverter 8 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 101,396 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 269160, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '9.045 KW on grid rooftop solar (335W -27P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (27 Qty)
                    - Inverter 10 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 111,028 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 305847, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => '9.715 KW on grid rooftop solar (335W -29P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (29 Qty)
                    - Inverter 10 kW with WIFI stick – 10 years warranty (1 Qty)
                    - AC protection device
                    - DC protection device
                    - DC / AC copper cable
                    - Earthing rod Copper coated 1.5 meter (Qty-3)
                    - Earthing 25 SQ Aluminium
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site
                    - SS cable tie
                    - Transportation
                    - Installation
                    - 5 years maintenance
                      (Subsidy amount – 117,204 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 330550, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],


                    ['name' => 'Industrial Rooftop On-grid Solar Power Plant', 'description' => trim('- 540-Watt Solar panel– 25 years warranty(Adani / Waree / Goldi)
                    - On Grid Solar Inverter (Sofar / Growatt / EVVO)
                    - Hot Dip GI Structure as per design
                    - ACDB with NVR & SPD
                    - DCDB with Fuse & MCB
                    - DC / AC copper cable as per requirement (Qty - As required)
                    - Earthing rod Copper coated 1.5 meter (Qty - As required)
                    - Earthing wire 25 SQ Aluminum
                    - Lightning arrestor (Qty – 1)
                    - PVC conduit as per site (Qty - As required)
                    - SS cable tie / MC4 & other Accessories
                    - Conduit & Cable Tray (As required)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 0, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],

                    ['name' => 'Installation & Commissioning', 'description' => trim('- Roof top solar power plant Installation as per design
                    - 5 years O&M
                    - Transportation & unloading /loading of material'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 0, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],

                ];
                DB::table('items')->insert($itemArr);

                // 8-PANEL
                $panel8T_1_path_one = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-1.jpg";
                $panel8T_1_one = date('YmdHis') . "1" . ".jpg";
                Image::make($panel8T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_one));
                Image::make($panel8T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_1_one));

                $panel8T_1_path_two = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-2.jpg";
                $panel8T_1_two = date('YmdHis') . "2" . ".jpg";
                Image::make($panel8T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_two));
                Image::make($panel8T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_1_two));

                $panel8T_1_path_three = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-3.jpg";
                $panel8T_1_three = date('YmdHis') . "3" . ".jpg";
                Image::make($panel8T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_three));
                Image::make($panel8T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_1_three));

                $panel8T_2_path_one = env('APP_URL') . "sample/product/8-PANEL/T-2/10002.jpg";
                $panel8T_2_one = date('YmdHis') . "4" . ".jpg";
                Image::make($panel8T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_one));
                Image::make($panel8T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_2_one));

                $panel8T_2_path_two = env('APP_URL') . "sample/product/8-PANEL/T-2/20001.jpg";
                $panel8T_2_two = date('YmdHis') . "5" . ".jpg";
                Image::make($panel8T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_two));
                Image::make($panel8T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_2_two));

                $panel8T_2_path_three = env('APP_URL') . "sample/product/8-PANEL/T-2/20003.jpg";
                $panel8T_2_three = date('YmdHis') . "6" . ".jpg";
                Image::make($panel8T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_three));
                Image::make($panel8T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_2_three));

                $panel8T_3_path_one = env('APP_URL') . "sample/product/8-PANEL/T-3/10002.jpg";
                $panel8T_3_one = date('YmdHis') . "7" . ".jpg";
                Image::make($panel8T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_one));
                Image::make($panel8T_3_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_3_one));

                $panel8T_3_path_two = env('APP_URL') . "sample/product/8-PANEL/T-3/20001.jpg";
                $panel8T_3_two = date('YmdHis') . "8" . ".jpg";
                Image::make($panel8T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_two));
                Image::make($panel8T_3_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_3_two));

                $panel8T_3_path_three = env('APP_URL') . "sample/product/8-PANEL/T-3/20003.jpg";
                $panel8T_3_three = date('YmdHis') . "9" . ".jpg";
                Image::make($panel8T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_three));
                Image::make($panel8T_3_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_3_three));

                // 9-PANEL
                $panel9T_1_path_one = env('APP_URL') . "sample/product/9-PANEL/T-1/10002.jpg";
                $panel9T_1_one = date('YmdHis') . "10" . ".jpg";
                Image::make($panel9T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_one));
                Image::make($panel9T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_1_one));

                $panel9T_1_path_two = env('APP_URL') . "sample/product/9-PANEL/T-1/20001.jpg";
                $panel9T_1_two = date('YmdHis') . "11" . ".jpg";
                Image::make($panel9T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_two));
                Image::make($panel9T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_1_two));

                $panel9T_1_path_three = env('APP_URL') . "sample/product/9-PANEL/T-1/20003.jpg";
                $panel9T_1_three = date('YmdHis') . "12" . ".jpg";
                Image::make($panel9T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_three));
                Image::make($panel9T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_1_three));

                $panel9T_2_path_one = env('APP_URL') . "sample/product/9-PANEL/T-2/10002.jpg";
                $panel9T_2_one = date('YmdHis') . "13" . ".jpg";
                Image::make($panel9T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_one));
                Image::make($panel9T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_2_one));

                $panel9T_2_path_two = env('APP_URL') . "sample/product/9-PANEL/T-2/20001.jpg";
                $panel9T_2_two = date('YmdHis') . "14" . ".jpg";
                Image::make($panel9T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_two));
                Image::make($panel9T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_2_two));

                $panel9T_2_path_three = env('APP_URL') . "sample/product/9-PANEL/T-2/20003.jpg";
                $panel9T_2_three = date('YmdHis') . "15" . ".jpg";
                Image::make($panel9T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_three));
                Image::make($panel9T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_2_three));

                $panel9T_3_path_one = env('APP_URL') . "sample/product/9-PANEL/T-3/10002.jpg";
                $panel9T_3_one = date('YmdHis') . "16" . ".jpg";
                Image::make($panel9T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_one));
                Image::make($panel9T_3_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_3_one));

                $panel9T_3_path_two = env('APP_URL') . "sample/product/9-PANEL/T-3/20001.jpg";
                $panel9T_3_two = date('YmdHis') . "17" . ".jpg";
                Image::make($panel9T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_two));
                Image::make($panel9T_3_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_3_two));

                $panel9T_3_path_three = env('APP_URL') . "sample/product/9-PANEL/T-3/20003.jpg";
                $panel9T_3_three = date('YmdHis') . "18" . ".jpg";
                Image::make($panel9T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_three));
                Image::make($panel9T_3_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_3_three));

                // 10-PANEL
                $panel10T_1_path_one = env('APP_URL') . "sample/product/10-PANEL/T-1/10002.jpg";
                $panel10T_1_one = date('YmdHis') . "19" . ".jpg";
                Image::make($panel10T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_one));
                Image::make($panel10T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_1_one));

                $panel10T_1_path_two = env('APP_URL') . "sample/product/10-PANEL/T-1/20001.jpg";
                $panel10T_1_two = date('YmdHis') . "20" . ".jpg";
                Image::make($panel10T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_two));
                Image::make($panel10T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_1_two));

                $panel10T_1_path_three = env('APP_URL') . "sample/product/10-PANEL/T-1/20003.jpg";
                $panel10T_1_three = date('YmdHis') . "21" . ".jpg";
                Image::make($panel10T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_three));
                Image::make($panel10T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_1_three));

                $panel10T_2_path_one = env('APP_URL') . "sample/product/10-PANEL/T-2/10002.jpg";
                $panel10T_2_one = date('YmdHis') . "22" . ".jpg";
                Image::make($panel10T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_one));
                Image::make($panel10T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_2_one));

                $panel10T_2_path_two = env('APP_URL') . "sample/product/10-PANEL/T-2/20001.jpg";
                $panel10T_2_two = date('YmdHis') . "23" . ".jpg";
                Image::make($panel10T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_two));
                Image::make($panel10T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_2_two));

                $panel10T_2_path_three = env('APP_URL') . "sample/product/10-PANEL/T-2/20003.jpg";
                $panel10T_2_three = date('YmdHis') . "24" . ".jpg";
                Image::make($panel10T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_three));
                Image::make($panel10T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_2_three));

                $panel10T_3_path_one = env('APP_URL') . "sample/product/10-PANEL/T-3/10002.jpg";
                $panel10T_3_one = date('YmdHis') . "25" . ".jpg";
                Image::make($panel10T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_one));
                Image::make($panel10T_3_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_3_one));

                $panel10T_3_path_two = env('APP_URL') . "sample/product/10-PANEL/T-3/20001.jpg";
                $panel10T_3_two = date('YmdHis') . "26" . ".jpg";
                Image::make($panel10T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_two));
                Image::make($panel10T_3_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_3_two));

                $panel10T_3_path_three = env('APP_URL') . "sample/product/10-PANEL/T-3/20003.jpg";
                $panel10T_3_three = date('YmdHis') . "27" . ".jpg";
                Image::make($panel10T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_three));
                Image::make($panel10T_3_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_3_three));

                // 11-PANEL
                $panel11T_1_path_one = env('APP_URL') . "sample/product/11-PANEL/T-1/10002.jpg";
                $panel11T_1_one = date('YmdHis') . "28" . ".jpg";
                Image::make($panel11T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_one));
                Image::make($panel11T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_1_one));

                $panel11T_1_path_two = env('APP_URL') . "sample/product/11-PANEL/T-1/20001.jpg";
                $panel11T_1_two = date('YmdHis') . "29" . ".jpg";
                Image::make($panel11T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_two));
                Image::make($panel11T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_1_two));

                $panel11T_1_path_three = env('APP_URL') . "sample/product/11-PANEL/T-1/20003.jpg";
                $panel11T_1_three = date('YmdHis') . "30" . ".jpg";
                Image::make($panel11T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_three));
                Image::make($panel11T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_1_three));

                $panel11T_2_path_one = env('APP_URL') . "sample/product/11-PANEL/T-2/10002.jpg";
                $panel11T_2_one = date('YmdHis') . "31" . ".jpg";
                Image::make($panel11T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_one));
                Image::make($panel11T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_2_one));

                $panel11T_2_path_two = env('APP_URL') . "sample/product/11-PANEL/T-2/20001.jpg";
                $panel11T_2_two = date('YmdHis') . "32" . ".jpg";
                Image::make($panel11T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_two));
                Image::make($panel11T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_2_two));

                $panel11T_2_path_three = env('APP_URL') . "sample/product/11-PANEL/T-2/20003.jpg";
                $panel11T_2_three = date('YmdHis') . "33" . ".jpg";
                Image::make($panel11T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_three));
                Image::make($panel11T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_2_three));

                // 12-PANEL
                $panel12T_1_path_one = env('APP_URL') . "sample/product/12-PANEL/T-1/10002.jpg";
                $panel12T_1_one = date('YmdHis') . "34" . ".jpg";
                Image::make($panel12T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_one));
                Image::make($panel12T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_1_one));

                $panel12T_1_path_two = env('APP_URL') . "sample/product/12-PANEL/T-1/20001.jpg";
                $panel12T_1_two = date('YmdHis') . "35" . ".jpg";
                Image::make($panel12T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_two));
                Image::make($panel12T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_1_two));

                $panel12T_1_path_three = env('APP_URL') . "sample/product/12-PANEL/T-1/20003.jpg";
                $panel12T_1_three = date('YmdHis') . "36" . ".jpg";
                Image::make($panel12T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_three));
                Image::make($panel12T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_1_three));

                $panel12T_2_path_one = env('APP_URL') . "sample/product/12-PANEL/T-2/10002.jpg";
                $panel12T_2_one = date('YmdHis') . "37" . ".jpg";
                Image::make($panel12T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_one));
                Image::make($panel12T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_2_one));

                $panel12T_2_path_two = env('APP_URL') . "sample/product/12-PANEL/T-2/20001.jpg";
                $panel12T_2_two = date('YmdHis') . "38" . ".jpg";
                Image::make($panel12T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_two));
                Image::make($panel12T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_2_two));

                $panel12T_2_path_three = env('APP_URL') . "sample/product/12-PANEL/T-2/20003.jpg";
                $panel12T_2_three = date('YmdHis') . "39" . ".jpg";
                Image::make($panel12T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_three));
                Image::make($panel12T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_2_three));

                $panel12T_3_path_one = env('APP_URL') . "sample/product/12-PANEL/T-3/10002.jpg";
                $panel12T_3_one = date('YmdHis') . "40" . ".jpg";
                Image::make($panel12T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_one));
                Image::make($panel12T_3_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_3_one));

                $panel12T_3_path_two = env('APP_URL') . "sample/product/12-PANEL/T-3/20001.jpg";
                $panel12T_3_two = date('YmdHis') . "41" . ".jpg";
                Image::make($panel12T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_two));
                Image::make($panel12T_3_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_3_two));

                $panel12T_3_path_three = env('APP_URL') . "sample/product/12-PANEL/T-3/20003.jpg";
                $panel12T_3_three = date('YmdHis') . "42" . ".jpg";
                Image::make($panel12T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_three));
                Image::make($panel12T_3_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_3_three));

                // 13-PANEL
                $panel13T_1_path_one = env('APP_URL') . "sample/product/13-PANEL/T-1/10002.jpg";
                $panel13T_1_one = date('YmdHis') . "43" . ".jpg";
                Image::make($panel13T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_one));
                Image::make($panel13T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_1_one));

                $panel13T_1_path_two = env('APP_URL') . "sample/product/13-PANEL/T-1/20001.jpg";
                $panel13T_1_two = date('YmdHis') . "44" . ".jpg";
                Image::make($panel13T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_two));
                Image::make($panel13T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_1_two));

                $panel13T_1_path_three = env('APP_URL') . "sample/product/13-PANEL/T-1/20003.jpg";
                $panel13T_1_three = date('YmdHis') . "45" . ".jpg";
                Image::make($panel13T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_three));
                Image::make($panel13T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_1_three));

                $panel13T_2_path_one = env('APP_URL') . "sample/product/13-PANEL/T-2/10002.jpg";
                $panel13T_2_one = date('YmdHis') . "46" . ".jpg";
                Image::make($panel13T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_one));
                Image::make($panel13T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_2_one));

                $panel13T_2_path_two = env('APP_URL') . "sample/product/13-PANEL/T-2/20001.jpg";
                $panel13T_2_two = date('YmdHis') . "47" . ".jpg";
                Image::make($panel13T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_two));
                Image::make($panel13T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_2_two));

                $panel13T_2_path_three = env('APP_URL') . "sample/product/13-PANEL/T-2/20003.jpg";
                $panel13T_2_three = date('YmdHis') . "48" . ".jpg";
                Image::make($panel13T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_three));
                Image::make($panel13T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_2_three));

                // 14-PANEL
                $panel14T_1_path_one = env('APP_URL') . "sample/product/14-PANEL/T-1/10002.jpg";
                $panel14T_1_one = date('YmdHis') . "49" . ".jpg";
                Image::make($panel14T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_one));
                Image::make($panel14T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_1_one));

                $panel14T_1_path_two = env('APP_URL') . "sample/product/14-PANEL/T-1/20001.jpg";
                $panel14T_1_two = date('YmdHis') . "50" . ".jpg";
                Image::make($panel14T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_two));
                Image::make($panel14T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_1_two));

                $panel14T_1_path_three = env('APP_URL') . "sample/product/14-PANEL/T-1/20003.jpg";
                $panel14T_1_three = date('YmdHis') . "51" . ".jpg";
                Image::make($panel14T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_three));
                Image::make($panel14T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_1_three));

                $panel14T_2_path_one = env('APP_URL') . "sample/product/14-PANEL/T-2/10002.jpg";
                $panel14T_2_one = date('YmdHis') . "52" . ".jpg";
                Image::make($panel14T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_one));
                Image::make($panel14T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_2_one));

                $panel14T_2_path_two = env('APP_URL') . "sample/product/14-PANEL/T-2/20001.jpg";
                $panel14T_2_two = date('YmdHis') . "53" . ".jpg";
                Image::make($panel14T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_two));
                Image::make($panel14T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_2_two));

                $panel14T_2_path_three = env('APP_URL') . "sample/product/14-PANEL/T-2/20003.jpg";
                $panel14T_2_three = date('YmdHis') . "54" . ".jpg";
                Image::make($panel14T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_three));
                Image::make($panel14T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_2_three));

                // 15-PANEL
                $panel15T_1_path_one = env('APP_URL') . "sample/product/15-PANEL/T-1/10002.jpg";
                $panel15T_1_one = date('YmdHis') . "55" . ".jpg";
                Image::make($panel15T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_one));
                Image::make($panel15T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_1_one));

                $panel15T_1_path_two = env('APP_URL') . "sample/product/15-PANEL/T-1/20001.jpg";
                $panel15T_1_two = date('YmdHis') . "56" . ".jpg";
                Image::make($panel15T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_two));
                Image::make($panel15T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_1_two));

                $panel15T_1_path_three = env('APP_URL') . "sample/product/15-PANEL/T-1/20003.jpg";
                $panel15T_1_three = date('YmdHis') . "57" . ".jpg";
                Image::make($panel15T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_three));
                Image::make($panel15T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_1_three));

                $panel15T_2_path_one = env('APP_URL') . "sample/product/15-PANEL/T-2/10002.jpg";
                $panel15T_2_one = date('YmdHis') . "58" . ".jpg";
                Image::make($panel15T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_one));
                Image::make($panel15T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_2_one));

                $panel15T_2_path_two = env('APP_URL') . "sample/product/15-PANEL/T-2/20001.jpg";
                $panel15T_2_two = date('YmdHis') . "59" . ".jpg";
                Image::make($panel15T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_two));
                Image::make($panel15T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_2_two));

                $panel15T_2_path_three = env('APP_URL') . "sample/product/15-PANEL/T-2/20003.jpg";
                $panel15T_2_three = date('YmdHis') . "60" . ".jpg";
                Image::make($panel15T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_three));
                Image::make($panel15T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_2_three));

                // 16-PANEL
                $panel16T_1_path_one = env('APP_URL') . "sample/product/16-PANEL/T-1/R010001.jpg";
                $panel16T_1_one = date('YmdHis') . "61" . ".jpg";
                Image::make($panel16T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_one));
                Image::make($panel16T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_1_one));

                $panel16T_1_path_two = env('APP_URL') . "sample/product/16-PANEL/T-1/R010002.jpg";
                $panel16T_1_two = date('YmdHis') . "62" . ".jpg";
                Image::make($panel16T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_two));
                Image::make($panel16T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_1_two));

                $panel16T_1_path_three = env('APP_URL') . "sample/product/16-PANEL/T-1/R010003.jpg";
                $panel16T_1_three = date('YmdHis') . "63" . ".jpg";
                Image::make($panel16T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_three));
                Image::make($panel16T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_1_three));

                $panel16T_2_path_one = env('APP_URL') . "sample/product/16-PANEL/T-2/3P6_160002.jpg";
                $panel16T_2_one = date('YmdHis') . "64" . ".jpg";
                Image::make($panel16T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_one));
                Image::make($panel16T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_2_one));

                $panel16T_2_path_two = env('APP_URL') . "sample/product/16-PANEL/T-2/10001.jpg";
                $panel16T_2_two = date('YmdHis') . "65" . ".jpg";
                Image::make($panel16T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_two));
                Image::make($panel16T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_2_two));

                $panel16T_2_path_three = env('APP_URL') . "sample/product/16-PANEL/T-2/10003.jpg";
                $panel16T_2_three = date('YmdHis') . "66" . ".jpg";
                Image::make($panel16T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_three));
                Image::make($panel16T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_2_three));

                // 17-PANEL
                $panel17T_1_path_one = env('APP_URL') . "sample/product/17-PANEL/T-1/R010002.jpg";
                $panel17T_1_one = date('YmdHis') . "67" . ".jpg";
                Image::make($panel17T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_one));
                Image::make($panel17T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel17T_1_one));

                $panel17T_1_path_two = env('APP_URL') . "sample/product/17-PANEL/T-1/R010001.jpg";
                $panel17T_1_two = date('YmdHis') . "68" . ".jpg";
                Image::make($panel17T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_two));
                Image::make($panel17T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel17T_1_two));

                $panel17T_1_path_three = env('APP_URL') . "sample/product/17-PANEL/T-1/R010003.jpg";
                $panel17T_1_three = date('YmdHis') . "69" . ".jpg";
                Image::make($panel17T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_three));
                Image::make($panel17T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel17T_1_three));

                // 18-PANEL
                $panel18T_1_path_one = env('APP_URL') . "sample/product/18-PANEL/T-1/R010002.jpg";
                $panel18T_1_one = date('YmdHis') . "70" . ".jpg";
                Image::make($panel18T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_one));
                Image::make($panel18T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_1_one));

                $panel18T_1_path_two = env('APP_URL') . "sample/product/18-PANEL/T-1/R010001.jpg";
                $panel18T_1_two = date('YmdHis') . "71" . ".jpg";
                Image::make($panel18T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_two));
                Image::make($panel18T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_1_two));

                $panel18T_1_path_three = env('APP_URL') . "sample/product/18-PANEL/T-1/R010003.jpg";
                $panel18T_1_three = date('YmdHis') . "72" . ".jpg";
                Image::make($panel18T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_three));
                Image::make($panel18T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_1_three));

                $panel18T_2_path_one = env('APP_URL') . "sample/product/18-PANEL/T-2/3P60002.jpg";
                $panel18T_2_one = date('YmdHis') . "73" . ".jpg";
                Image::make($panel18T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_one));
                Image::make($panel18T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_2_one));

                $panel18T_2_path_two = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0001.jpg";
                $panel18T_2_two = date('YmdHis') . "74" . ".jpg";
                Image::make($panel18T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_two));
                Image::make($panel18T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_2_two));

                $panel18T_2_path_three = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0003.jpg";
                $panel18T_2_three = date('YmdHis') . "75" . ".jpg";
                Image::make($panel18T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_three));
                Image::make($panel18T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_2_three));

                // 21-PANEL
                $panel21T_1_path_one = env('APP_URL') . "sample/product/21-PANEL/T-1/R010002.jpg";
                $panel21T_1_one = date('YmdHis') . "76" . ".jpg";
                Image::make($panel21T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_one));
                Image::make($panel21T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_1_one));

                $panel21T_1_path_two = env('APP_URL') . "sample/product/21-PANEL/T-1/R010001.jpg";
                $panel21T_1_two = date('YmdHis') . "77" . ".jpg";
                Image::make($panel21T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_two));
                Image::make($panel21T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_1_two));

                $panel21T_1_path_three = env('APP_URL') . "sample/product/21-PANEL/T-1/R010003.jpg";
                $panel21T_1_three = date('YmdHis') . "78" . ".jpg";
                Image::make($panel21T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_three));
                Image::make($panel21T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_1_three));

                $panel21T_2_path_one = env('APP_URL') . "sample/product/21-PANEL/T-2/3P70002.jpg";
                $panel21T_2_one = date('YmdHis') . "79" . ".jpg";
                Image::make($panel21T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_one));
                Image::make($panel21T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_2_one));

                $panel21T_2_path_two = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0001.jpg";
                $panel21T_2_two = date('YmdHis') . "80" . ".jpg";
                Image::make($panel21T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_two));
                Image::make($panel21T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_2_two));

                $panel21T_2_path_three = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0003.jpg";
                $panel21T_2_three = date('YmdHis') . "81" . ".jpg";
                Image::make($panel21T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_three));
                Image::make($panel21T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_2_three));

                // 24-PANEL
                $panel24T_1_path_one = env('APP_URL') . "sample/product/24-PANEL/T-1/R010002.jpg";
                $panel24T_1_one = date('YmdHis') . "82" . ".jpg";
                Image::make($panel24T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_one));
                Image::make($panel24T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_1_one));

                $panel24T_1_path_two = env('APP_URL') . "sample/product/24-PANEL/T-1/R010001.jpg";
                $panel24T_1_two = date('YmdHis') . "83" . ".jpg";
                Image::make($panel24T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_two));
                Image::make($panel24T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_1_two));

                $panel24T_1_path_three = env('APP_URL') . "sample/product/24-PANEL/T-1/R010003.jpg";
                $panel24T_1_three = date('YmdHis') . "84" . ".jpg";
                Image::make($panel24T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_three));
                Image::make($panel24T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_1_three));

                $panel24T_2_path_one = env('APP_URL') . "sample/product/24-PANEL/T-2/10002.jpg";
                $panel24T_2_one = date('YmdHis') . "85" . ".jpg";
                Image::make($panel24T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_one));
                Image::make($panel24T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_2_one));

                $panel24T_2_path_two = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0001.jpg";
                $panel24T_2_two = date('YmdHis') . "86" . ".jpg";
                Image::make($panel24T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_two));
                Image::make($panel24T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_2_two));

                $panel24T_2_path_three = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0003.jpg";
                $panel24T_2_three = date('YmdHis') . "87" . ".jpg";
                Image::make($panel24T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_three));
                Image::make($panel24T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_2_three));

                // 27-PANEL
                $panel27T_1_path_one = env('APP_URL') . "sample/product/27-PANEL/T-1/R010002.jpg";
                $panel27T_1_one = date('YmdHis') . "88" . ".jpg";
                Image::make($panel27T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_one));
                Image::make($panel27T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_1_one));

                $panel27T_1_path_two = env('APP_URL') . "sample/product/27-PANEL/T-1/R010001.jpg";
                $panel27T_1_two = date('YmdHis') . "89" . ".jpg";
                Image::make($panel27T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_two));
                Image::make($panel27T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_1_two));

                $panel27T_1_path_three = env('APP_URL') . "sample/product/27-PANEL/T-1/R010003.jpg";
                $panel27T_1_three = date('YmdHis') . "90" . ".jpg";
                Image::make($panel27T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_three));
                Image::make($panel27T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_1_three));

                $panel27T_2_path_one = env('APP_URL') . "sample/product/27-PANEL/T-2/10002.jpg";
                $panel27T_2_one = date('YmdHis') . "91" . ".jpg";
                Image::make($panel27T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_one));
                Image::make($panel27T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_2_one));

                $panel27T_2_path_two = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0001.jpg";
                $panel27T_2_two = date('YmdHis') . "92" . ".jpg";
                Image::make($panel27T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_two));
                Image::make($panel27T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_2_two));

                $panel27T_2_path_three = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0003.jpg";
                $panel27T_2_three = date('YmdHis') . "93" . ".jpg";
                Image::make($panel27T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_three));
                Image::make($panel27T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_2_three));

                // 29-PANEL
                $panel29T_1_path_one = env('APP_URL') . "sample/product/29-PANEL/T-1/R010001.jpg";
                $panel29T_1_one = date('YmdHis') . "94" . ".jpg";
                Image::make($panel29T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_one));
                Image::make($panel29T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_1_one));

                $panel29T_1_path_two = env('APP_URL') . "sample/product/29-PANEL/T-1/R010002.jpg";
                $panel29T_1_two = date('YmdHis') . "95" . ".jpg";
                Image::make($panel29T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_two));
                Image::make($panel29T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_1_two));

                $panel29T_1_path_three = env('APP_URL') . "sample/product/29-PANEL/T-1/R010003.jpg";
                $panel29T_1_three = date('YmdHis') . "96" . ".jpg";
                Image::make($panel29T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_three));
                Image::make($panel29T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_1_three));

                $panel29T_2_path_one = env('APP_URL') . "sample/product/29-PANEL/T-2/3P100002.jpg";
                $panel29T_2_one = date('YmdHis') . "97" . ".jpg";
                Image::make($panel29T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_one));
                Image::make($panel29T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_2_one));

                $panel29T_2_path_two = env('APP_URL') . "sample/product/29-PANEL/T-2/R010001.jpg";
                $panel29T_2_two = date('YmdHis') . "98" . ".jpg";
                Image::make($panel29T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_two));
                Image::make($panel29T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_2_two));

                $panel29T_2_path_three = env('APP_URL') . "sample/product/29-PANEL/T-2/R010003.jpg";
                $panel29T_2_three = date('YmdHis') . "99" . ".jpg";
                Image::make($panel29T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_three));
                Image::make($panel29T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_2_three));


                // 30-PANEL
                $panel30T_1_path_one = env('APP_URL') . "sample/product/30-PANEL/T-1/R010001.jpg";
                $panel30T_1_one = date('YmdHis') . "100" . ".jpg";
                Image::make($panel30T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_one));
                Image::make($panel30T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_1_one));

                $panel30T_1_path_two = env('APP_URL') . "sample/product/30-PANEL/T-1/R010002.jpg";
                $panel30T_1_two = date('YmdHis') . "101" . ".jpg";
                Image::make($panel30T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_two));
                Image::make($panel30T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_1_two));

                $panel30T_1_path_three = env('APP_URL') . "sample/product/30-PANEL/T-1/R010003.jpg";
                $panel30T_1_three = date('YmdHis') . "102" . ".jpg";
                Image::make($panel30T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_three));
                Image::make($panel30T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_1_three));

                $panel30T_2_path_one = env('APP_URL') . "sample/product/30-PANEL/T-2/3P100002.jpg";
                $panel30T_2_one = date('YmdHis') . "103" . ".jpg";
                Image::make($panel30T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_one));
                Image::make($panel30T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_2_one));

                $panel30T_2_path_two = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010001.jpg";
                $panel30T_2_two = date('YmdHis') . "104" . ".jpg";
                Image::make($panel30T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_two));
                Image::make($panel30T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_2_two));

                $panel30T_2_path_three = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010003.jpg";
                $panel30T_2_three = date('YmdHis') . "105" . ".jpg";
                Image::make($panel30T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_three));
                Image::make($panel30T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_2_three));

                $commercial_path_one = env('APP_URL') . "sample/product/commercial/10002.jpg";
                $commercial_one = rand(1000, 9999) . ".jpg";
                Image::make($commercial_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_one));
                Image::make($commercial_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_one));

                $commercial_path_two = env('APP_URL') . "sample/product/commercial/20001.jpg";
                $commercial_two = rand(1000, 9999) . ".jpg";
                Image::make($commercial_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_two));
                Image::make($commercial_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_two));

                $commercial_path_three = env('APP_URL') . "sample/product/commercial/20003.jpg";
                $commercial_three = rand(1000, 9999) . ".jpg";
                Image::make($commercial_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_three));
                Image::make($commercial_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_three));


                $productArr = [
                    [
                        'name' => '8 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel8T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel8T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel8T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel8T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel8T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel8T_1_three,
                        'status' => 1,
                        'user_id' => $tmpId,
                        'company_id' => $tmpId
                    ],
                    [
                        'name' => '8 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel8T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel8T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel8T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel8T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel8T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel8T_2_three,
                        'status' => 1,
                        'user_id' => $tmpId,
                        'company_id' => $tmpId
                    ],
                    [
                        'name' => '8 Panel_T-3',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel8T_3_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel8T_3_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel8T_3_three,
                        'thumb_thumb_image_one' => 'public/uploads/resize_image/' . $panel8T_3_one,
                        'thumb_thumb_image_two' => 'public/uploads/resize_image/' . $panel8T_3_two,
                        'thumb_thumb_image_three' => 'public/uploads/resize_image/' . $panel8T_3_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '9 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel9T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel9T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel9T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel9T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel9T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel9T_1_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    [
                        'name' => '9 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel9T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel9T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel9T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel9T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel9T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel9T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    [
                        'name' => '9 Panel_T-3',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel9T_3_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel9T_3_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel9T_3_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel9T_3_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel9T_3_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel9T_3_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '10 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel10T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel10T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel10T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel10T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel10T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel10T_1_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '10 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel10T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel10T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel10T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel10T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel10T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel10T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '10 Panel_T-3',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel10T_3_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel10T_3_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel10T_3_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel10T_3_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel10T_3_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel10T_3_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '11 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel11T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel11T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel11T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel11T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel11T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel11T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '11 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel11T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel11T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel11T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel11T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel11T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel11T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '12 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel12T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel12T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel12T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel12T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel12T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel12T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '12 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel12T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel12T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel12T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel12T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel12T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel12T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '12 Panel_T-3',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel12T_3_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel12T_3_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel12T_3_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel12T_3_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel12T_3_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel12T_3_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '13 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel13T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel13T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel13T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel13T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel13T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel13T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '13 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel13T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel13T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel13T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel13T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel13T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel13T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '14 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel14T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel14T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel14T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel14T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel14T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel14T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '14 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel14T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel14T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel14T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel14T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel14T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel14T_2_three,
                        'status' => 1,
                        'user_id' => $tmpId,
                        'company_id' => $tmpId
                    ],
                    [
                        'name' => '15 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel15T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel15T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel15T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel15T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel15T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel15T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '15 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel15T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel15T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel15T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel15T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel15T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel15T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '16 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel16T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel16T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel16T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel16T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel16T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel16T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '16 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel16T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel16T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel16T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel16T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel16T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel16T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '17 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel17T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel17T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel17T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel17T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel17T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel17T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '18 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel18T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel18T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel18T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel18T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel18T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel18T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '18 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel18T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel18T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel18T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel18T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel18T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel18T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '21 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel21T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel21T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel21T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel21T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel21T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel21T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '21 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel21T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel21T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel21T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel21T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel21T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel21T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '24 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel24T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel24T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel24T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel24T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel24T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel24T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '24 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel24T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel24T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel24T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel24T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel24T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel24T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '27 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel27T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel27T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel27T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel27T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel27T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel27T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '27 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel27T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel27T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel27T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel27T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel27T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel27T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '29 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel29T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel29T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel29T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel29T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel29T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel29T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '29 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel29T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel29T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel29T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel29T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel29T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel29T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '30 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel30T_1_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel30T_1_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel30T_1_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel30T_1_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel30T_1_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel30T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '30 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/uploads/thumbnail/' . $panel30T_2_one,
                        'image_two' => 'public/uploads/thumbnail/' . $panel30T_2_two,
                        'image_three' => 'public/uploads/thumbnail/' . $panel30T_2_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $panel30T_2_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $panel30T_2_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $panel30T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => 'Commercial Installations',
                        'description' => 'Our Previous Project Installation Photos',
                        'image_one' => 'public/uploads/thumbnail/' . $commercial_one,
                        'image_two' => 'public/uploads/thumbnail/' . $commercial_two,
                        'image_three' => 'public/uploads/thumbnail/' . $commercial_three,
                        'thumb_image_one' => 'public/uploads/resize_image/' . $commercial_one,
                        'thumb_image_two' => 'public/uploads/resize_image/' . $commercial_two,
                        'thumb_image_three' => 'public/uploads/resize_image/' . $commercial_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                ];
                DB::table('products')->insert($productArr);
            }*/
            if ($userDatas->company_category == 1) {
                $path_one = env('APP_URL') . "sample/testimonial/t-1.png";
                $filename_one = 'public/'.$tmpId.'/testimonials/'.date('YmdHis') . "106" . ".png";
//                Image::make($path_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_one));
                Storage::disk('s3')->put($filename_one, file_get_contents($path_one),'public');
                $publicUrl = Storage::disk('s3')->url($filename_one);

                $path_two = env('APP_URL') . "sample/testimonial/t-2.png";
                $filename_two = 'public/'.$tmpId.'/testimonials/'.date('YmdHis') . "107" . ".png";
//                Image::make($path_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_two));
                Storage::disk('s3')->put($filename_two, file_get_contents($path_two),'public');
                $publicUrl = Storage::disk('s3')->url($filename_two);

                $path_three = env('APP_URL') . "sample/testimonial/t-3.png";
                $filename_three = 'public/'.$tmpId.'/testimonials/'.date('YmdHis') . "108" . ".png";
//                Image::make($path_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_three));
                Storage::disk('s3')->put($filename_three, file_get_contents($path_three),'public');
                $publicUrl = Storage::disk('s3')->url($filename_three);

                $path_four = env('APP_URL') . "sample/testimonial/t-7.jpeg";

                $filename_four = 'public/'.$tmpId.'/testimonials/'.date('YmdHis') . "507" . ".png";
                Storage::disk('s3')->put($filename_four, file_get_contents($path_four),'public');
                $publicUrl = Storage::disk('s3')->url($filename_four);

                $path_five = env('APP_URL') . "sample/testimonial/t-8.jpeg";
                $filename_five = 'public/'.$tmpId.'/testimonials/'.date('YmdHis') . "508" . ".png";
                Storage::disk('s3')->put($filename_five, file_get_contents($path_five),'public');
                $publicUrl = Storage::disk('s3')->url($filename_five);

                $path_six = env('APP_URL') . "sample/testimonial/t-9.jpeg";
                $filename_six = 'public/'.$tmpId.'/testimonials/'.date('YmdHis') . "509" . ".png";
                Storage::disk('s3')->put($filename_six, file_get_contents($path_six),'public');
                $publicUrl = Storage::disk('s3')->url($filename_six);
                $testimonialArr = [
                    [
                        'name' => 'Residential Testimonial',
                        'client_name_one' => 'Rahulbhai patel',
                        'client_name_two' => 'Payalben hirpara',
                        'client_name_three' => 'Kiranbhai prajapati',
                        'description_one' => 'I recently installed solar on my rooftop. Thank You team for neat and clean Installation on my rooftop. Also, received my subsidy. Great Work team. Thanks',
                        'description_two' => 'When you have empty roof then why to pay for electricity bill? Thank You for end to end guidance. Your staff is very professional and friendly. Thanks for making my roof solarize! Superb Work by Team.',
                        'description_three' => 'One of the best decision of my life to go solar! I really appreciate your product quality and workmanship. In last 6 month, my plant has generated more than 2000 Units and counting. I strongly recommend everyone to go solar as soon as possible. Thank You',
                        'rating_one' => 5,
                        'rating_two' => 5,
                        'rating_three' => 5,
                        'image_one' => $filename_one,
                        'image_two' => $filename_two,
                        'image_three' => $filename_three,
                        'status' => 0,
                        'is_default' => 1,
                        'user_id' => $tmpId,
                        'company_id' => $tmpId,
                    ],
                    [
                        'name' => 'Commercial Clients',
                        'client_name_one' => 'Mr. Mitesh Dhakesh',
                        'client_name_two' => 'Mr. Chinmay Modi',
                        'client_name_three' => 'Mr. Divyesh Patel',
                        'description_one' => 'Absolutely, heres a concise review for your solar installer:

"Extremely satisfied with rooftop solar power plant! The installation was smooth, professional, and on time. Great customer service and excellent quality.',
                        'description_two' => 'When you have empty roof then why to pay for electricity bill? Thank You for end to end guidance. Your staff is very professional and friendly. Thanks for making my roof solarize! Superb Work by Team.',
                        'description_three' => 'One of the best decision of my life to go solar! I really appreciate your product quality and workmanship. In last 6 month, my plant has generated more than 2000 Units and counting. I strongly recommend everyone to go solar as soon as possible. Thank You',
                        'rating_one' => 5,
                        'rating_two' => 5,
                        'rating_three' => 5,
                        /*'image_one' => 'public/uploads/thumbnail/' . $filename_one,
                        'image_two' => 'public/uploads/thumbnail/' . $filename_two,
                        'image_three' => 'public/uploads/thumbnail/' . $filename_three,*/
                        'image_one' => $filename_four,
                        'image_two' => $filename_five,
                        'image_three' => $filename_six,
                        'status' => 0,
                        'is_default' => 0,
                        'user_id' => $tmpId,
                        'company_id' => $tmpId,
                    ]

                ];
                DB::table('testimonials')->insert($testimonialArr);
                $units1 = Unit::where([["company_id", "=", $tmpId], ["name", "=", "Nos"]])->orderBy('id', 'ASC')->select("id")->first();
                $itemArr = [
                    ['name' => 'Commercial Solar Power Plant', 'description' => trim('On Grid Solar Power Plant ___ KW
Solar Panel Brand
Solar Inverter Brand

Please find Detailed BOM'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 13.8, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Taxable', 'sale_price' => 49000.00, 'sales_flag' => 1, 'status' => 0, 'purchase_flag' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Residential Solar Rooftop solar Plant', 'description' => trim('1.Solar Power Plant Capacity : 3.24  KW
2.Solar Panels Brand: Adani Mono 540 W (6 Qty)
3.Solar Inverter Brand: KSolare 3.2 KW

Please find detailed BOM & Scope on next page.
78,000 Subsidy will be credited to your bank account direct.'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 13.8, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Taxable', 'sale_price' => 49000.00, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Solar Meter Charges', 'description' => trim('This charge we need to pay to Government (Discom)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units1->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 3250.00, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Solar Structure Charge', 'description' => '', 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 18, 'cost_price' => 0, 'tax_preference' => 'Taxable', 'sale_price' => 8500.00, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Subsidy Amount', 'description' => '', 'item_type' => 'Goods', 'unit_id' => $units1->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 58000.00, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId]
                ];
                DB::table('items')->insert($itemArr);

                // 8-PANEL
                $panel8T_1_path_one = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-1.jpg";
                $panel8T_1_one = date('YmdHis') . "1" . ".jpg";
                /*Image::make($panel8T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_one));
                Image::make($panel8T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_1_one));*/

                // Copy the original image to S3
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel8T_1_one, file_get_contents($panel8T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel8T_1_one);
                $image = Image::make($panel8T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel8T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel8T_1_one);

                $panel8T_1_path_two = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-2.jpg";
                $panel8T_1_two = date('YmdHis') . "2" . ".jpg";
                /* Image::make($panel8T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_two));
                 Image::make($panel8T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_1_two));*/

                // Copy the original image to S3
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel8T_1_two, file_get_contents($panel8T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel8T_1_two);
                $image = Image::make($panel8T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel8T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel8T_1_two);

                $panel8T_1_path_three = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-3.jpg";
                $panel8T_1_three = date('YmdHis') . "3" . ".jpg";
//                Image::make($panel8T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_three));
//                Image::make($panel8T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_1_three));
                // Copy the original image to S3
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel8T_1_three, file_get_contents($panel8T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel8T_1_three);
                $image = Image::make($panel8T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel8T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel8T_1_three);

                $panel8T_2_path_one = env('APP_URL') . "sample/product/8-PANEL/T-2/10002.jpg";
                $panel8T_2_one = date('YmdHis') . "4" . ".jpg";
//                Image::make($panel8T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_one));
//                Image::make($panel8T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_2_one));
                // Copy the original image to S3
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel8T_2_one, file_get_contents($panel8T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel8T_2_one);
                $image = Image::make($panel8T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel8T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel8T_2_one);

                $panel8T_2_path_two = env('APP_URL') . "sample/product/8-PANEL/T-2/20001.jpg";
                $panel8T_2_two = date('YmdHis') . "5" . ".jpg";
//                Image::make($panel8T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_two));
//                Image::make($panel8T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel8T_2_two, file_get_contents($panel8T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel8T_2_two);
                $image = Image::make($panel8T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel8T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel8T_2_two);

                $panel8T_2_path_three = env('APP_URL') . "sample/product/8-PANEL/T-2/20003.jpg";
                $panel8T_2_three = date('YmdHis') . "6" . ".jpg";
//                Image::make($panel8T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_three));
//                Image::make($panel8T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel8T_2_three, file_get_contents($panel8T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel8T_2_three);
                $image = Image::make($panel8T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel8T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel8T_2_three);

                $panel8T_3_path_one = env('APP_URL') . "sample/product/8-PANEL/T-3/10002.jpg";
                $panel8T_3_one = date('YmdHis') . "7" . ".jpg";
//                Image::make($panel8T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_one));
//                Image::make($panel8T_3_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_3_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel8T_3_one, file_get_contents($panel8T_3_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel8T_3_one);
                $image = Image::make($panel8T_3_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel8T_3_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel8T_3_one);

                $panel8T_3_path_two = env('APP_URL') . "sample/product/8-PANEL/T-3/20001.jpg";
                $panel8T_3_two = date('YmdHis') . "8" . ".jpg";
//                Image::make($panel8T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_two));
//                Image::make($panel8T_3_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_3_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel8T_3_two, file_get_contents($panel8T_3_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel8T_3_two);
                $image = Image::make($panel8T_3_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel8T_3_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel8T_3_two);

                $panel8T_3_path_three = env('APP_URL') . "sample/product/8-PANEL/T-3/20003.jpg";
                $panel8T_3_three = date('YmdHis') . "9" . ".jpg";
//                Image::make($panel8T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_three));
//                Image::make($panel8T_3_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel8T_3_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel8T_3_three, file_get_contents($panel8T_3_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel8T_3_three);
                $image = Image::make($panel8T_3_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel8T_3_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel8T_3_three);

                // 9-PANEL
                $panel9T_1_path_one = env('APP_URL') . "sample/product/9-PANEL/T-1/10002.jpg";
                $panel9T_1_one = date('YmdHis') . "10" . ".jpg";
//                Image::make($panel9T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_one));
//                Image::make($panel9T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel9T_1_one, file_get_contents($panel9T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel9T_1_one);
                $image = Image::make($panel9T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel9T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel9T_1_one);

                $panel9T_1_path_two = env('APP_URL') . "sample/product/9-PANEL/T-1/20001.jpg";
                $panel9T_1_two = date('YmdHis') . "11" . ".jpg";
//                Image::make($panel9T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_two));
//                Image::make($panel9T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel9T_1_two, file_get_contents($panel9T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel9T_1_two);
                $image = Image::make($panel9T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel9T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel9T_1_two);

                $panel9T_1_path_three = env('APP_URL') . "sample/product/9-PANEL/T-1/20003.jpg";
                $panel9T_1_three = date('YmdHis') . "12" . ".jpg";
//                Image::make($panel9T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_three));
//                Image::make($panel9T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel9T_1_three, file_get_contents($panel9T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel9T_1_three);
                $image = Image::make($panel9T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel9T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel9T_1_three);

                $panel9T_2_path_one = env('APP_URL') . "sample/product/9-PANEL/T-2/10002.jpg";
                $panel9T_2_one = date('YmdHis') . "13" . ".jpg";
//                Image::make($panel9T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_one));
//                Image::make($panel9T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel9T_2_one, file_get_contents($panel9T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel9T_2_one);
                $image = Image::make($panel9T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel9T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel9T_2_one);

                $panel9T_2_path_two = env('APP_URL') . "sample/product/9-PANEL/T-2/20001.jpg";
                $panel9T_2_two = date('YmdHis') . "14" . ".jpg";
//                Image::make($panel9T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_two));
//                Image::make($panel9T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel9T_2_two, file_get_contents($panel9T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel9T_2_two);
                $image = Image::make($panel9T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel9T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel9T_2_two);

                $panel9T_2_path_three = env('APP_URL') . "sample/product/9-PANEL/T-2/20003.jpg";
                $panel9T_2_three = date('YmdHis') . "15" . ".jpg";
//                Image::make($panel9T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_three));
//                Image::make($panel9T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel9T_2_three, file_get_contents($panel9T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel9T_2_three);
                $image = Image::make($panel9T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel9T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel9T_2_three);

                $panel9T_3_path_one = env('APP_URL') . "sample/product/9-PANEL/T-3/10002.jpg";
                $panel9T_3_one = date('YmdHis') . "16" . ".jpg";
//                Image::make($panel9T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_one));
//                Image::make($panel9T_3_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_3_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel9T_3_one, file_get_contents($panel9T_3_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel9T_3_one);
                $image = Image::make($panel9T_3_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel9T_3_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel9T_3_one);

                $panel9T_3_path_two = env('APP_URL') . "sample/product/9-PANEL/T-3/20001.jpg";
                $panel9T_3_two = date('YmdHis') . "17" . ".jpg";
//                Image::make($panel9T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_two));
//                Image::make($panel9T_3_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_3_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel9T_3_two, file_get_contents($panel9T_3_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel9T_3_two);
                $image = Image::make($panel9T_3_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel9T_3_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel9T_3_two);

                $panel9T_3_path_three = env('APP_URL') . "sample/product/9-PANEL/T-3/20003.jpg";
                $panel9T_3_three = date('YmdHis') . "18" . ".jpg";
//                Image::make($panel9T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_three));
//                Image::make($panel9T_3_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel9T_3_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel9T_3_three, file_get_contents($panel9T_3_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel9T_3_three);
                $image = Image::make($panel9T_3_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel9T_3_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel9T_3_three);

                // 10-PANEL
                $panel10T_1_path_one = env('APP_URL') . "sample/product/10-PANEL/T-1/10002.jpg";
                $panel10T_1_one = date('YmdHis') . "19" . ".jpg";
//                Image::make($panel10T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_one));
//                Image::make($panel10T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel10T_1_one, file_get_contents($panel10T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel10T_1_one);
                $image = Image::make($panel10T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel10T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel10T_1_one);

                $panel10T_1_path_two = env('APP_URL') . "sample/product/10-PANEL/T-1/20001.jpg";
                $panel10T_1_two = date('YmdHis') . "20" . ".jpg";
//                Image::make($panel10T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_two));
//                Image::make($panel10T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel10T_1_two, file_get_contents($panel10T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel10T_1_two);
                $image = Image::make($panel10T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel10T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel10T_1_two);

                $panel10T_1_path_three = env('APP_URL') . "sample/product/10-PANEL/T-1/20003.jpg";
                $panel10T_1_three = date('YmdHis') . "21" . ".jpg";
//                Image::make($panel10T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_three));
//                Image::make($panel10T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel10T_1_three, file_get_contents($panel10T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel10T_1_three);
                $image = Image::make($panel10T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel10T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel10T_1_three);

                $panel10T_2_path_one = env('APP_URL') . "sample/product/10-PANEL/T-2/10002.jpg";
                $panel10T_2_one = date('YmdHis') . "22" . ".jpg";
//                Image::make($panel10T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_one));
//                Image::make($panel10T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel10T_2_one, file_get_contents($panel10T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel10T_2_one);
                $image = Image::make($panel10T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel10T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel10T_2_one);

                $panel10T_2_path_two = env('APP_URL') . "sample/product/10-PANEL/T-2/20001.jpg";
                $panel10T_2_two = date('YmdHis') . "23" . ".jpg";
//                Image::make($panel10T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_two));
//                Image::make($panel10T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel10T_2_two, file_get_contents($panel10T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel10T_2_two);
                $image = Image::make($panel10T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel10T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel10T_2_two);

                $panel10T_2_path_three = env('APP_URL') . "sample/product/10-PANEL/T-2/20003.jpg";
                $panel10T_2_three = date('YmdHis') . "24" . ".jpg";
//                Image::make($panel10T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_three));
//                Image::make($panel10T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel10T_2_three, file_get_contents($panel10T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel10T_2_three);
                $image = Image::make($panel10T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel10T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel10T_2_three);

                $panel10T_3_path_one = env('APP_URL') . "sample/product/10-PANEL/T-3/10002.jpg";
                $panel10T_3_one = date('YmdHis') . "25" . ".jpg";
//                Image::make($panel10T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_one));
//                Image::make($panel10T_3_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_3_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel10T_3_one, file_get_contents($panel10T_3_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel10T_3_one);
                $image = Image::make($panel10T_3_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel10T_3_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel10T_3_one);

                $panel10T_3_path_two = env('APP_URL') . "sample/product/10-PANEL/T-3/20001.jpg";
                $panel10T_3_two = date('YmdHis') . "26" . ".jpg";
//                Image::make($panel10T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_two));
//                Image::make($panel10T_3_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_3_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel10T_3_two, file_get_contents($panel10T_3_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel10T_3_two);
                $image = Image::make($panel10T_3_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel10T_3_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel10T_3_two);

                $panel10T_3_path_three = env('APP_URL') . "sample/product/10-PANEL/T-3/20003.jpg";
                $panel10T_3_three = date('YmdHis') . "27" . ".jpg";
//                Image::make($panel10T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_three));
//                Image::make($panel10T_3_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel10T_3_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel10T_3_three, file_get_contents($panel10T_3_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel10T_3_three);
                $image = Image::make($panel10T_3_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel10T_3_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel10T_3_three);

                // 11-PANEL
                $panel11T_1_path_one = env('APP_URL') . "sample/product/11-PANEL/T-1/10002.jpg";
                $panel11T_1_one = date('YmdHis') . "28" . ".jpg";
//                Image::make($panel11T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_one));
//                Image::make($panel11T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel11T_1_one, file_get_contents($panel11T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel11T_1_one);
                $image = Image::make($panel11T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel11T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel11T_1_one);

                $panel11T_1_path_two = env('APP_URL') . "sample/product/11-PANEL/T-1/20001.jpg";
                $panel11T_1_two = date('YmdHis') . "29" . ".jpg";
//                Image::make($panel11T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_two));
//                Image::make($panel11T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel11T_1_two, file_get_contents($panel11T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel11T_1_two);
                $image = Image::make($panel11T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel11T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel11T_1_two);

                $panel11T_1_path_three = env('APP_URL') . "sample/product/11-PANEL/T-1/20003.jpg";
                $panel11T_1_three = date('YmdHis') . "30" . ".jpg";
//                Image::make($panel11T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_three));
//                Image::make($panel11T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel11T_1_three, file_get_contents($panel11T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel11T_1_three);
                $image = Image::make($panel11T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel11T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel11T_1_three);

                $panel11T_2_path_one = env('APP_URL') . "sample/product/11-PANEL/T-2/10002.jpg";
                $panel11T_2_one = date('YmdHis') . "31" . ".jpg";
//                Image::make($panel11T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_one));
//                Image::make($panel11T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel11T_2_one, file_get_contents($panel11T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel11T_2_one);
                $image = Image::make($panel11T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel11T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel11T_2_one);

                $panel11T_2_path_two = env('APP_URL') . "sample/product/11-PANEL/T-2/20001.jpg";
                $panel11T_2_two = date('YmdHis') . "32" . ".jpg";
//                Image::make($panel11T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_two));
//                Image::make($panel11T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel11T_2_two, file_get_contents($panel11T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel11T_2_two);
                $image = Image::make($panel11T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel11T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel11T_2_two);

                $panel11T_2_path_three = env('APP_URL') . "sample/product/11-PANEL/T-2/20003.jpg";
                $panel11T_2_three = date('YmdHis') . "33" . ".jpg";
//                Image::make($panel11T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_three));
//                Image::make($panel11T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel11T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel11T_2_three, file_get_contents($panel11T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel11T_2_three);
                $image = Image::make($panel11T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel11T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel11T_2_three);

                // 12-PANEL
                $panel12T_1_path_one = env('APP_URL') . "sample/product/12-PANEL/T-1/10002.jpg";
                $panel12T_1_one = date('YmdHis') . "34" . ".jpg";
//                Image::make($panel12T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_one));
//                Image::make($panel12T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel12T_1_one, file_get_contents($panel12T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel12T_1_one);
                $image = Image::make($panel12T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel12T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel12T_1_one);

                $panel12T_1_path_two = env('APP_URL') . "sample/product/12-PANEL/T-1/20001.jpg";
                $panel12T_1_two = date('YmdHis') . "35" . ".jpg";
//                Image::make($panel12T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_two));
//                Image::make($panel12T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel12T_1_two, file_get_contents($panel12T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel12T_1_two);
                $image = Image::make($panel12T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel12T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel12T_1_two);

                $panel12T_1_path_three = env('APP_URL') . "sample/product/12-PANEL/T-1/20003.jpg";
                $panel12T_1_three = date('YmdHis') . "36" . ".jpg";
//                Image::make($panel12T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_three));
//                Image::make($panel12T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel12T_1_three, file_get_contents($panel12T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel12T_1_three);
                $image = Image::make($panel12T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel12T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel12T_1_three);

                $panel12T_2_path_one = env('APP_URL') . "sample/product/12-PANEL/T-2/10002.jpg";
                $panel12T_2_one = date('YmdHis') . "37" . ".jpg";
//                Image::make($panel12T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_one));
//                Image::make($panel12T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel12T_2_one, file_get_contents($panel12T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel12T_2_one);
                $image = Image::make($panel12T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel12T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel12T_2_one);

                $panel12T_2_path_two = env('APP_URL') . "sample/product/12-PANEL/T-2/20001.jpg";
                $panel12T_2_two = date('YmdHis') . "38" . ".jpg";
//                Image::make($panel12T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_two));
//                Image::make($panel12T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel12T_2_two, file_get_contents($panel12T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel12T_2_two);
                $image = Image::make($panel12T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel12T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel12T_2_two);

                $panel12T_2_path_three = env('APP_URL') . "sample/product/12-PANEL/T-2/20003.jpg";
                $panel12T_2_three = date('YmdHis') . "39" . ".jpg";
//                Image::make($panel12T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_three));
//                Image::make($panel12T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel12T_2_three, file_get_contents($panel12T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel12T_2_three);
                $image = Image::make($panel12T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel12T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel12T_2_three);

                $panel12T_3_path_one = env('APP_URL') . "sample/product/12-PANEL/T-3/10002.jpg";
                $panel12T_3_one = date('YmdHis') . "40" . ".jpg";
//                Image::make($panel12T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_one));
//                Image::make($panel12T_3_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_3_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel12T_3_one, file_get_contents($panel12T_3_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel12T_3_one);
                $image = Image::make($panel12T_3_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel12T_3_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel12T_3_one);

                $panel12T_3_path_two = env('APP_URL') . "sample/product/12-PANEL/T-3/20001.jpg";
                $panel12T_3_two = date('YmdHis') . "41" . ".jpg";
//                Image::make($panel12T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_two));
//                Image::make($panel12T_3_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_3_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel12T_3_two, file_get_contents($panel12T_3_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel12T_3_two);
                $image = Image::make($panel12T_3_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel12T_3_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel12T_3_two);

                $panel12T_3_path_three = env('APP_URL') . "sample/product/12-PANEL/T-3/20003.jpg";
                $panel12T_3_three = date('YmdHis') . "42" . ".jpg";
//                Image::make($panel12T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_three));
//                Image::make($panel12T_3_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel12T_3_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel12T_3_three, file_get_contents($panel12T_3_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel12T_3_three);
                $image = Image::make($panel12T_3_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel12T_3_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel12T_3_three);

                // 13-PANEL
                $panel13T_1_path_one = env('APP_URL') . "sample/product/13-PANEL/T-1/10002.jpg";
                $panel13T_1_one = date('YmdHis') . "43" . ".jpg";
//                Image::make($panel13T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_one));
//                Image::make($panel13T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel13T_1_one, file_get_contents($panel13T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel13T_1_one);
                $image = Image::make($panel13T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel13T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel13T_1_one);

                $panel13T_1_path_two = env('APP_URL') . "sample/product/13-PANEL/T-1/20001.jpg";
                $panel13T_1_two = date('YmdHis') . "44" . ".jpg";
//                Image::make($panel13T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_two));
//                Image::make($panel13T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel13T_1_two, file_get_contents($panel13T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel13T_1_two);
                $image = Image::make($panel13T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel13T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel13T_1_two);

                $panel13T_1_path_three = env('APP_URL') . "sample/product/13-PANEL/T-1/20003.jpg";
                $panel13T_1_three = date('YmdHis') . "45" . ".jpg";
//                Image::make($panel13T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_three));
//                Image::make($panel13T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel13T_1_three, file_get_contents($panel13T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel13T_1_three);
                $image = Image::make($panel13T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel13T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel13T_1_three);

                $panel13T_2_path_one = env('APP_URL') . "sample/product/13-PANEL/T-2/10002.jpg";
                $panel13T_2_one = date('YmdHis') . "46" . ".jpg";
//                Image::make($panel13T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_one));
//                Image::make($panel13T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel13T_2_one, file_get_contents($panel13T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel13T_2_one);
                $image = Image::make($panel13T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel13T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel13T_2_one);

                $panel13T_2_path_two = env('APP_URL') . "sample/product/13-PANEL/T-2/20001.jpg";
                $panel13T_2_two = date('YmdHis') . "47" . ".jpg";
//                Image::make($panel13T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_two));
//                Image::make($panel13T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel13T_2_two, file_get_contents($panel13T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel13T_2_two);
                $image = Image::make($panel13T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel13T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel13T_2_two);

                $panel13T_2_path_three = env('APP_URL') . "sample/product/13-PANEL/T-2/20003.jpg";
                $panel13T_2_three = date('YmdHis') . "48" . ".jpg";
//                Image::make($panel13T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_three));
//                Image::make($panel13T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel13T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel13T_2_three, file_get_contents($panel13T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel13T_2_three);
                $image = Image::make($panel13T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel13T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel13T_2_three);

                // 14-PANEL
                $panel14T_1_path_one = env('APP_URL') . "sample/product/14-PANEL/T-1/10002.jpg";
                $panel14T_1_one = date('YmdHis') . "49" . ".jpg";
//                Image::make($panel14T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_one));
//                Image::make($panel14T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel14T_1_one, file_get_contents($panel14T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel14T_1_one);
                $image = Image::make($panel14T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel14T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel14T_1_one);

                $panel14T_1_path_two = env('APP_URL') . "sample/product/14-PANEL/T-1/20001.jpg";
                $panel14T_1_two = date('YmdHis') . "50" . ".jpg";
//                Image::make($panel14T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_two));
//                Image::make($panel14T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel14T_1_two, file_get_contents($panel14T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel14T_1_two);
                $image = Image::make($panel14T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel14T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel14T_1_two);

                $panel14T_1_path_three = env('APP_URL') . "sample/product/14-PANEL/T-1/20003.jpg";
                $panel14T_1_three = date('YmdHis') . "51" . ".jpg";
//                Image::make($panel14T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_three));
//                Image::make($panel14T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel14T_1_three, file_get_contents($panel14T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel14T_1_three);
                $image = Image::make($panel14T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel14T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel14T_1_three);

                $panel14T_2_path_one = env('APP_URL') . "sample/product/14-PANEL/T-2/10002.jpg";
                $panel14T_2_one = date('YmdHis') . "52" . ".jpg";
//                Image::make($panel14T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_one));
//                Image::make($panel14T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel14T_2_one, file_get_contents($panel14T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel14T_2_one);
                $image = Image::make($panel14T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel14T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel14T_2_one);

                $panel14T_2_path_two = env('APP_URL') . "sample/product/14-PANEL/T-2/20001.jpg";
                $panel14T_2_two = date('YmdHis') . "53" . ".jpg";
//                Image::make($panel14T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_two));
//                Image::make($panel14T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel14T_2_two, file_get_contents($panel14T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel14T_2_two);
                $image = Image::make($panel14T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel14T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel14T_2_two);

                $panel14T_2_path_three = env('APP_URL') . "sample/product/14-PANEL/T-2/20003.jpg";
                $panel14T_2_three = date('YmdHis') . "54" . ".jpg";
//                Image::make($panel14T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_three));
//                Image::make($panel14T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel14T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel14T_2_three, file_get_contents($panel14T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel14T_2_three);
                $image = Image::make($panel14T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel14T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel14T_2_three);

                // 15-PANEL
                $panel15T_1_path_one = env('APP_URL') . "sample/product/15-PANEL/T-1/10002.jpg";
                $panel15T_1_one = date('YmdHis') . "55" . ".jpg";
//                Image::make($panel15T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_one));
//                Image::make($panel15T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel15T_1_one, file_get_contents($panel15T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel15T_1_one);
                $image = Image::make($panel15T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel15T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel15T_1_one);

                $panel15T_1_path_two = env('APP_URL') . "sample/product/15-PANEL/T-1/20001.jpg";
                $panel15T_1_two = date('YmdHis') . "56" . ".jpg";
//                Image::make($panel15T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_two));
//                Image::make($panel15T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel15T_1_two, file_get_contents($panel15T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel15T_1_two);
                $image = Image::make($panel15T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel15T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel15T_1_two);

                $panel15T_1_path_three = env('APP_URL') . "sample/product/15-PANEL/T-1/20003.jpg";
                $panel15T_1_three = date('YmdHis') . "57" . ".jpg";
//                Image::make($panel15T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_three));
//                Image::make($panel15T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel15T_1_three, file_get_contents($panel15T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel15T_1_three);
                $image = Image::make($panel15T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel15T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel15T_1_three);

                $panel15T_2_path_one = env('APP_URL') . "sample/product/15-PANEL/T-2/10002.jpg";
                $panel15T_2_one = date('YmdHis') . "58" . ".jpg";
//                Image::make($panel15T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_one));
//                Image::make($panel15T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel15T_2_one, file_get_contents($panel15T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel15T_2_one);
                $image = Image::make($panel15T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel15T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel15T_2_one);

                $panel15T_2_path_two = env('APP_URL') . "sample/product/15-PANEL/T-2/20001.jpg";
                $panel15T_2_two = date('YmdHis') . "59" . ".jpg";
//                Image::make($panel15T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_two));
//                Image::make($panel15T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel15T_2_two, file_get_contents($panel15T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel15T_2_two);
                $image = Image::make($panel15T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel15T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel15T_2_two);

                $panel15T_2_path_three = env('APP_URL') . "sample/product/15-PANEL/T-2/20003.jpg";
                $panel15T_2_three = date('YmdHis') . "60" . ".jpg";
//                Image::make($panel15T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_three));
//                Image::make($panel15T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel15T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel15T_2_three, file_get_contents($panel15T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel15T_2_three);
                $image = Image::make($panel15T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel15T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel15T_2_three);

                // 16-PANEL
                $panel16T_1_path_one = env('APP_URL') . "sample/product/16-PANEL/T-1/R010001.jpg";
                $panel16T_1_one = date('YmdHis') . "61" . ".jpg";
//                Image::make($panel16T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_one));
//                Image::make($panel16T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel16T_1_one, file_get_contents($panel16T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel16T_1_one);
                $image = Image::make($panel16T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel16T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel16T_1_one);

                $panel16T_1_path_two = env('APP_URL') . "sample/product/16-PANEL/T-1/R010002.jpg";
                $panel16T_1_two = date('YmdHis') . "62" . ".jpg";
//                Image::make($panel16T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_two));
//                Image::make($panel16T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel16T_1_two, file_get_contents($panel16T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel16T_1_two);
                $image = Image::make($panel16T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel16T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel16T_1_two);

                $panel16T_1_path_three = env('APP_URL') . "sample/product/16-PANEL/T-1/R010003.jpg";
                $panel16T_1_three = date('YmdHis') . "63" . ".jpg";
//                Image::make($panel16T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_three));
//                Image::make($panel16T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel16T_1_three, file_get_contents($panel16T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel16T_1_three);
                $image = Image::make($panel16T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel16T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel16T_1_three);

                $panel16T_2_path_one = env('APP_URL') . "sample/product/16-PANEL/T-2/3P6_160002.jpg";
                $panel16T_2_one = date('YmdHis') . "64" . ".jpg";
//                Image::make($panel16T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_one));
//                Image::make($panel16T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel16T_2_one, file_get_contents($panel16T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel16T_2_one);
                $image = Image::make($panel16T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel16T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel16T_2_one);

                $panel16T_2_path_two = env('APP_URL') . "sample/product/16-PANEL/T-2/10001.jpg";
                $panel16T_2_two = date('YmdHis') . "65" . ".jpg";
//                Image::make($panel16T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_two));
//                Image::make($panel16T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel16T_2_two, file_get_contents($panel16T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel16T_2_two);
                $image = Image::make($panel16T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel16T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel16T_2_two);

                $panel16T_2_path_three = env('APP_URL') . "sample/product/16-PANEL/T-2/10003.jpg";
                $panel16T_2_three = date('YmdHis') . "66" . ".jpg";
//                Image::make($panel16T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_three));
//                Image::make($panel16T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel16T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel16T_2_three, file_get_contents($panel16T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel16T_2_three);
                $image = Image::make($panel16T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel16T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel16T_2_three);

                // 17-PANEL
                $panel17T_1_path_one = env('APP_URL') . "sample/product/17-PANEL/T-1/R010002.jpg";
                $panel17T_1_one = date('YmdHis') . "67" . ".jpg";
//                Image::make($panel17T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_one));
//                Image::make($panel17T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel17T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel17T_1_one, file_get_contents($panel17T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel17T_1_one);
                $image = Image::make($panel17T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel17T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel17T_1_one);

                $panel17T_1_path_two = env('APP_URL') . "sample/product/17-PANEL/T-1/R010001.jpg";
                $panel17T_1_two = date('YmdHis') . "68" . ".jpg";
//                Image::make($panel17T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_two));
//                Image::make($panel17T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel17T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel17T_1_two, file_get_contents($panel17T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel17T_1_two);
                $image = Image::make($panel17T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel17T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel17T_1_two);

                $panel17T_1_path_three = env('APP_URL') . "sample/product/17-PANEL/T-1/R010003.jpg";
                $panel17T_1_three = date('YmdHis') . "69" . ".jpg";
//                Image::make($panel17T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_three));
//                Image::make($panel17T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel17T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel17T_1_three, file_get_contents($panel17T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel17T_1_three);
                $image = Image::make($panel17T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel17T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel17T_1_three);

                // 18-PANEL
                $panel18T_1_path_one = env('APP_URL') . "sample/product/18-PANEL/T-1/R010002.jpg";
                $panel18T_1_one = date('YmdHis') . "70" . ".jpg";
//                Image::make($panel18T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_one));
//                Image::make($panel18T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel18T_1_one, file_get_contents($panel18T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel18T_1_one);
                $image = Image::make($panel18T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel18T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel18T_1_one);

                $panel18T_1_path_two = env('APP_URL') . "sample/product/18-PANEL/T-1/R010001.jpg";
                $panel18T_1_two = date('YmdHis') . "71" . ".jpg";
//                Image::make($panel18T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_two));
//                Image::make($panel18T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel18T_1_two, file_get_contents($panel18T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel18T_1_two);
                $image = Image::make($panel18T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel18T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel18T_1_two);

                $panel18T_1_path_three = env('APP_URL') . "sample/product/18-PANEL/T-1/R010003.jpg";
                $panel18T_1_three = date('YmdHis') . "72" . ".jpg";
//                Image::make($panel18T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_three));
//                Image::make($panel18T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel18T_1_three, file_get_contents($panel18T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel18T_1_three);
                $image = Image::make($panel18T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel18T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel18T_1_three);

                $panel18T_2_path_one = env('APP_URL') . "sample/product/18-PANEL/T-2/3P60002.jpg";
                $panel18T_2_one = date('YmdHis') . "73" . ".jpg";
//                Image::make($panel18T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_one));
//                Image::make($panel18T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel18T_2_one, file_get_contents($panel18T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel18T_2_one);
                $image = Image::make($panel18T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel18T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel18T_2_one);

                $panel18T_2_path_two = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0001.jpg";
                $panel18T_2_two = date('YmdHis') . "74" . ".jpg";
//                Image::make($panel18T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_two));
//                Image::make($panel18T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel18T_2_two, file_get_contents($panel18T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel18T_2_two);
                $image = Image::make($panel18T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel18T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel18T_2_two);

                $panel18T_2_path_three = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0003.jpg";
                $panel18T_2_three = date('YmdHis') . "75" . ".jpg";
//                Image::make($panel18T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_three));
//                Image::make($panel18T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel18T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel18T_2_three, file_get_contents($panel18T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel18T_2_three);
                $image = Image::make($panel18T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel18T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel18T_2_three);

                // 21-PANEL
                $panel21T_1_path_one = env('APP_URL') . "sample/product/21-PANEL/T-1/R010002.jpg";
                $panel21T_1_one = date('YmdHis') . "76" . ".jpg";
//                Image::make($panel21T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_one));
//                Image::make($panel21T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel21T_1_one, file_get_contents($panel21T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel21T_1_one);
                $image = Image::make($panel21T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel21T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel21T_1_one);

                $panel21T_1_path_two = env('APP_URL') . "sample/product/21-PANEL/T-1/R010001.jpg";
                $panel21T_1_two = date('YmdHis') . "77" . ".jpg";
//                Image::make($panel21T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_two));
//                Image::make($panel21T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel21T_1_two, file_get_contents($panel21T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel21T_1_two);
                $image = Image::make($panel21T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel21T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel21T_1_two);

                $panel21T_1_path_three = env('APP_URL') . "sample/product/21-PANEL/T-1/R010003.jpg";
                $panel21T_1_three = date('YmdHis') . "78" . ".jpg";
//                Image::make($panel21T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_three));
//                Image::make($panel21T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel21T_1_three, file_get_contents($panel21T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel21T_1_three);
                $image = Image::make($panel21T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel21T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel21T_1_three);

                $panel21T_2_path_one = env('APP_URL') . "sample/product/21-PANEL/T-2/3P70002.jpg";
                $panel21T_2_one = date('YmdHis') . "79" . ".jpg";
//                Image::make($panel21T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_one));
//                Image::make($panel21T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel21T_2_one, file_get_contents($panel21T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel21T_2_one);
                $image = Image::make($panel21T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel21T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel21T_2_one);

                $panel21T_2_path_two = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0001.jpg";
                $panel21T_2_two = date('YmdHis') . "80" . ".jpg";
//                Image::make($panel21T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_two));
//                Image::make($panel21T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel21T_2_two, file_get_contents($panel21T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel21T_2_two);
                $image = Image::make($panel21T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel21T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel21T_2_two);

                $panel21T_2_path_three = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0003.jpg";
                $panel21T_2_three = date('YmdHis') . "81" . ".jpg";
//                Image::make($panel21T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_three));
//                Image::make($panel21T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel21T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel21T_2_three, file_get_contents($panel21T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel21T_2_three);
                $image = Image::make($panel21T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel21T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel21T_2_three);

                // 24-PANEL
                $panel24T_1_path_one = env('APP_URL') . "sample/product/24-PANEL/T-1/R010002.jpg";
                $panel24T_1_one = date('YmdHis') . "82" . ".jpg";
//                Image::make($panel24T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_one));
//                Image::make($panel24T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel24T_1_one, file_get_contents($panel24T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel24T_1_one);
                $image = Image::make($panel24T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel24T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel24T_1_one);

                $panel24T_1_path_two = env('APP_URL') . "sample/product/24-PANEL/T-1/R010001.jpg";
                $panel24T_1_two = date('YmdHis') . "83" . ".jpg";
//                Image::make($panel24T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_two));
//                Image::make($panel24T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel24T_1_two, file_get_contents($panel24T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel24T_1_two);
                $image = Image::make($panel24T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel24T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel24T_1_two);

                $panel24T_1_path_three = env('APP_URL') . "sample/product/24-PANEL/T-1/R010003.jpg";
                $panel24T_1_three = date('YmdHis') . "84" . ".jpg";
//                Image::make($panel24T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_three));
//                Image::make($panel24T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel24T_1_three, file_get_contents($panel24T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel24T_1_three);
                $image = Image::make($panel24T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel24T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel24T_1_three);

                $panel24T_2_path_one = env('APP_URL') . "sample/product/24-PANEL/T-2/10002.jpg";
                $panel24T_2_one = date('YmdHis') . "85" . ".jpg";
//                Image::make($panel24T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_one));
//                Image::make($panel24T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel24T_2_one, file_get_contents($panel24T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel24T_2_one);
                $image = Image::make($panel24T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel24T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel24T_2_one);

                $panel24T_2_path_two = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0001.jpg";
                $panel24T_2_two = date('YmdHis') . "86" . ".jpg";
//                Image::make($panel24T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_two));
//                Image::make($panel24T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel24T_2_two, file_get_contents($panel24T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel24T_2_two);
                $image = Image::make($panel24T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel24T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel24T_2_two);

                $panel24T_2_path_three = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0003.jpg";
                $panel24T_2_three = date('YmdHis') . "87" . ".jpg";
//                Image::make($panel24T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_three));
//                Image::make($panel24T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel24T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel24T_2_three, file_get_contents($panel24T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel24T_2_three);
                $image = Image::make($panel24T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel24T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel24T_2_three);

                // 27-PANEL
                $panel27T_1_path_one = env('APP_URL') . "sample/product/27-PANEL/T-1/R010002.jpg";
                $panel27T_1_one = date('YmdHis') . "88" . ".jpg";
//                Image::make($panel27T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_one));
//                Image::make($panel27T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel27T_1_one, file_get_contents($panel27T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel27T_1_one);
                $image = Image::make($panel27T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel27T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel27T_1_one);

                $panel27T_1_path_two = env('APP_URL') . "sample/product/27-PANEL/T-1/R010001.jpg";
                $panel27T_1_two = date('YmdHis') . "89" . ".jpg";
//                Image::make($panel27T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_two));
//                Image::make($panel27T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel27T_1_two, file_get_contents($panel27T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel27T_1_two);
                $image = Image::make($panel27T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel27T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel27T_1_two);

                $panel27T_1_path_three = env('APP_URL') . "sample/product/27-PANEL/T-1/R010003.jpg";
                $panel27T_1_three = date('YmdHis') . "90" . ".jpg";
//                Image::make($panel27T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_three));
//                Image::make($panel27T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel27T_1_three, file_get_contents($panel27T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel27T_1_three);
                $image = Image::make($panel27T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel27T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel27T_1_three);

                $panel27T_2_path_one = env('APP_URL') . "sample/product/27-PANEL/T-2/10002.jpg";
                $panel27T_2_one = date('YmdHis') . "91" . ".jpg";
//                Image::make($panel27T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_one));
//                Image::make($panel27T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel27T_2_one, file_get_contents($panel27T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel27T_2_one);
                $image = Image::make($panel27T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel27T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel27T_2_one);

                $panel27T_2_path_two = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0001.jpg";
                $panel27T_2_two = date('YmdHis') . "92" . ".jpg";
//                Image::make($panel27T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_two));
//                Image::make($panel27T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel27T_2_two, file_get_contents($panel27T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel27T_2_two);
                $image = Image::make($panel27T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel27T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel27T_2_two);

                $panel27T_2_path_three = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0003.jpg";
                $panel27T_2_three = date('YmdHis') . "93" . ".jpg";
//                Image::make($panel27T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_three));
//                Image::make($panel27T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel27T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel27T_2_three, file_get_contents($panel27T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel27T_2_three);
                $image = Image::make($panel27T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel27T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel27T_2_three);

                // 29-PANEL
                $panel29T_1_path_one = env('APP_URL') . "sample/product/29-PANEL/T-1/R010001.jpg";
                $panel29T_1_one = date('YmdHis') . "94" . ".jpg";
//                Image::make($panel29T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_one));
//                Image::make($panel29T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel29T_1_one, file_get_contents($panel29T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel29T_1_one);
                $image = Image::make($panel29T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel29T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel29T_1_one);

                $panel29T_1_path_two = env('APP_URL') . "sample/product/29-PANEL/T-1/R010002.jpg";
                $panel29T_1_two = date('YmdHis') . "95" . ".jpg";
//                Image::make($panel29T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_two));
//                Image::make($panel29T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel29T_1_two, file_get_contents($panel29T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel29T_1_two);
                $image = Image::make($panel29T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel29T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel29T_1_two);

                $panel29T_1_path_three = env('APP_URL') . "sample/product/29-PANEL/T-1/R010003.jpg";
                $panel29T_1_three = date('YmdHis') . "96" . ".jpg";
//                Image::make($panel29T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_three));
//                Image::make($panel29T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel29T_1_three, file_get_contents($panel29T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel29T_1_three);
                $image = Image::make($panel29T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel29T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel29T_1_three);

                $panel29T_2_path_one = env('APP_URL') . "sample/product/29-PANEL/T-2/3P100002.jpg";
                $panel29T_2_one = date('YmdHis') . "97" . ".jpg";
//                Image::make($panel29T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_one));
//                Image::make($panel29T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel29T_2_one, file_get_contents($panel29T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel29T_2_one);
                $image = Image::make($panel29T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel29T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel29T_2_one);

                $panel29T_2_path_two = env('APP_URL') . "sample/product/29-PANEL/T-2/R010001.jpg";
                $panel29T_2_two = date('YmdHis') . "98" . ".jpg";
//                Image::make($panel29T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_two));
//                Image::make($panel29T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel29T_2_two, file_get_contents($panel29T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel29T_2_two);
                $image = Image::make($panel29T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel29T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel29T_2_two);

                $panel29T_2_path_three = env('APP_URL') . "sample/product/29-PANEL/T-2/R010003.jpg";
                $panel29T_2_three = date('YmdHis') . "99" . ".jpg";
//                Image::make($panel29T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_three));
//                Image::make($panel29T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel29T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel29T_2_three, file_get_contents($panel29T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel29T_2_three);
                $image = Image::make($panel29T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel29T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel29T_2_three);


                // 30-PANEL
                $panel30T_1_path_one = env('APP_URL') . "sample/product/30-PANEL/T-1/R010001.jpg";
                $panel30T_1_one = date('YmdHis') . "100" . ".jpg";
//                Image::make($panel30T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_one));
//                Image::make($panel30T_1_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_1_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel30T_1_one, file_get_contents($panel30T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel30T_1_one);
                $image = Image::make($panel30T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel30T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel30T_1_one);

                $panel30T_1_path_two = env('APP_URL') . "sample/product/30-PANEL/T-1/R010002.jpg";
                $panel30T_1_two = date('YmdHis') . "101" . ".jpg";
//                Image::make($panel30T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_two));
//                Image::make($panel30T_1_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_1_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel30T_1_two, file_get_contents($panel30T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel30T_1_two);
                $image = Image::make($panel30T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel30T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel30T_1_two);

                $panel30T_1_path_three = env('APP_URL') . "sample/product/30-PANEL/T-1/R010003.jpg";
                $panel30T_1_three = date('YmdHis') . "102" . ".jpg";
//                Image::make($panel30T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_three));
//                Image::make($panel30T_1_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_1_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel30T_1_three, file_get_contents($panel30T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel30T_1_three);
                $image = Image::make($panel30T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel30T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel30T_1_three);

                $panel30T_2_path_one = env('APP_URL') . "sample/product/30-PANEL/T-2/3P100002.jpg";
                $panel30T_2_one = date('YmdHis') . "103" . ".jpg";
//                Image::make($panel30T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_one));
//                Image::make($panel30T_2_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_2_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel30T_2_one, file_get_contents($panel30T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel30T_2_one);
                $image = Image::make($panel30T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel30T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel30T_2_one);

                $panel30T_2_path_two = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010001.jpg";
                $panel30T_2_two = date('YmdHis') . "104" . ".jpg";
//                Image::make($panel30T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_two));
//                Image::make($panel30T_2_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_2_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel30T_2_two, file_get_contents($panel30T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel30T_2_two);
                $image = Image::make($panel30T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel30T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel30T_2_two);

                $panel30T_2_path_three = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010003.jpg";
                $panel30T_2_three = date('YmdHis') . "105" . ".jpg";
//                Image::make($panel30T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_three));
//                Image::make($panel30T_2_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $panel30T_2_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $panel30T_2_three, file_get_contents($panel30T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $panel30T_2_three);
                $image = Image::make($panel30T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $panel30T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $panel30T_2_three);

                $commercial_path_one = env('APP_URL') . "sample/product/commercial/10002.jpg";
                $commercial_one = rand(1000, 9999) . ".jpg";
//                Image::make($commercial_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_one));
//                Image::make($commercial_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $commercial_one, file_get_contents($commercial_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $commercial_one);
                $image = Image::make($commercial_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $commercial_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $commercial_one);

                $commercial_path_two = env('APP_URL') . "sample/product/commercial/20001.jpg";
                $commercial_two = rand(1000, 9999) . ".jpg";
//                Image::make($commercial_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_two));
//                Image::make($commercial_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $commercial_two, file_get_contents($commercial_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $commercial_two);
                $image = Image::make($commercial_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $commercial_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $commercial_two);

                $commercial_path_three = env('APP_URL') . "sample/product/commercial/20003.jpg";
                $commercial_three = rand(1000, 9999) . ".jpg";
//                Image::make($commercial_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_three));
//                Image::make($commercial_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $commercial_three, file_get_contents($commercial_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $commercial_three);
                $image = Image::make($commercial_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $commercial_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $commercial_three);

                //4-Mono-T-1
                $m4T_1_path_one = env('APP_URL') . "sample/product/4-Mono-T-1/1.jpeg";
                $m4T_1_one = date('YmdHis') . "900" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m4T_1_one, file_get_contents($m4T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m4T_1_one);
                $image = Image::make($m4T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m4T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m4T_1_one);

                $m4T_1_path_two = env('APP_URL') . "sample/product/4-Mono-T-1/2.jpeg";
                $m4T_1_two = date('YmdHis') . "902" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m4T_1_two, file_get_contents($m4T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m4T_1_two);
                $image = Image::make($m4T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m4T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m4T_1_two);

                $m4T_1_path_three = env('APP_URL') . "sample/product/4-Mono-T-1/3.jpeg";
                $m4T_1_three = date('YmdHis') . "903" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m4T_1_three, file_get_contents($m4T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m4T_1_three);
                $image = Image::make($m4T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m4T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m4T_1_three);

                //4-Mono-T-2
                $m4T_2_path_one = env('APP_URL') . "sample/product/4-Mono-T-2/1.jpeg";
                $m4T_2_one = date('YmdHis') . "904" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m4T_2_one, file_get_contents($m4T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m4T_2_one);
                $image = Image::make($m4T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m4T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m4T_2_one);

                $m4T_2_path_two = env('APP_URL') . "sample/product/4-Mono-T-2/2.jpeg";
                $m4T_2_two = date('YmdHis') . "905" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m4T_2_two, file_get_contents($m4T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m4T_2_two);
                $image = Image::make($m4T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m4T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m4T_2_two);

                $m4T_2_path_three = env('APP_URL') . "sample/product/4-Mono-T-2/3.jpeg";
                $m4T_2_three = date('YmdHis') . "906" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m4T_2_three, file_get_contents($m4T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m4T_2_three);
                $image = Image::make($m4T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m4T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m4T_2_three);

                //6-Mono-T-1
                $m6T_1_path_one = env('APP_URL') . "sample/product/6-Mono-T-1/1.jpeg";
                $m6T_1_one = date('YmdHis') . "907" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m6T_1_one, file_get_contents($m6T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m6T_1_one);
                $image = Image::make($m6T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m6T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m6T_1_one);

                $m6T_1_path_two = env('APP_URL') . "sample/product/6-Mono-T-1/2.jpeg";
                $m6T_1_two = date('YmdHis') . "908" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m6T_1_two, file_get_contents($m6T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m6T_1_two);
                $image = Image::make($m6T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m6T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m6T_1_two);

                $m6T_1_path_three = env('APP_URL') . "sample/product/6-Mono-T-1/3.jpeg";
                $m6T_1_three = date('YmdHis') . "909" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m6T_1_three, file_get_contents($m6T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m6T_1_three);
                $image = Image::make($m6T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m6T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m6T_1_three);


                //6-Mono-T-2
                $m6T_2_path_one = env('APP_URL') . "sample/product/6-Mono-T-2/1.jpeg";
                $m6T_2_one = date('YmdHis') . "910" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m6T_2_one, file_get_contents($m6T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m6T_2_one);
                $image = Image::make($m6T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m6T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m6T_2_one);

                $m6T_2_path_two = env('APP_URL') . "sample/product/6-Mono-T-2/2.jpeg";
                $m6T_2_two = date('YmdHis') . "911" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m6T_2_two, file_get_contents($m6T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m6T_2_two);
                $image = Image::make($m6T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m6T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m6T_2_two);

                $m6T_2_path_three = env('APP_URL') . "sample/product/6-Mono-T-2/3.jpeg";
                $m6T_2_three = date('YmdHis') . "912" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m6T_2_three, file_get_contents($m6T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m6T_2_three);
                $image = Image::make($m6T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m6T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m6T_2_three);

                //6-Mono-T-3
                $m6T_3_path_one = env('APP_URL') . "sample/product/6-Mono-T-3/1.jpeg";
                $m6T_3_one = date('YmdHis') . "913" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m6T_3_one, file_get_contents($m6T_3_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m6T_3_one);
                $image = Image::make($m6T_3_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m6T_3_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m6T_3_one);

                $m6T_3_path_two = env('APP_URL') . "sample/product/6-Mono-T-3/2.jpeg";
                $m6T_3_two = date('YmdHis') . "914" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m6T_3_two, file_get_contents($m6T_3_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m6T_3_two);
                $image = Image::make($m6T_3_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m6T_3_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m6T_3_two);

                $m6T_3_path_three = env('APP_URL') . "sample/product/6-Mono-T-3/3.jpeg";
                $m6T_3_three = date('YmdHis') . "915" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m6T_3_three, file_get_contents($m6T_3_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m6T_3_three);
                $image = Image::make($m6T_3_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m6T_3_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m6T_3_three);

                //7-Mono-T-1
                $m7T_1_path_one = env('APP_URL') . "sample/product/7-Mono-T-1/1.jpeg";
                $m7T_1_one = date('YmdHis') . "916" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m7T_1_one, file_get_contents($m7T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m7T_1_one);
                $image = Image::make($m7T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m7T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m7T_1_one);

                $m7T_1_path_two = env('APP_URL') . "sample/product/7-Mono-T-1/2.jpeg";
                $m7T_1_two = date('YmdHis') . "917" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m7T_1_two, file_get_contents($m7T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m7T_1_two);
                $image = Image::make($m7T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m7T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m7T_1_two);

                $m7T_1_path_three = env('APP_URL') . "sample/product/7-Mono-T-1/3.jpeg";
                $m7T_1_three = date('YmdHis') . "918" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m7T_1_three, file_get_contents($m7T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m7T_1_three);
                $image = Image::make($m7T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m7T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m7T_1_three);


                //7-Mono-T-2
                $m7T_2_path_one = env('APP_URL') . "sample/product/7-Mono-T-2/1.jpeg";
                $m7T_2_one = date('YmdHis') . "919" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m7T_2_one, file_get_contents($m7T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m7T_2_one);
                $image = Image::make($m7T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m7T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m7T_2_one);

                $m7T_2_path_two = env('APP_URL') . "sample/product/7-Mono-T-2/2.jpeg";
                $m7T_2_two = date('YmdHis') . "920" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m7T_2_two, file_get_contents($m7T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m7T_2_two);
                $image = Image::make($m7T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m7T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m7T_2_two);

                $m7T_2_path_three = env('APP_URL') . "sample/product/7-Mono-T-2/3.jpeg";
                $m7T_2_three = date('YmdHis') . "921" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m7T_2_three, file_get_contents($m7T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m7T_2_three);
                $image = Image::make($m7T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m7T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m7T_2_three);

                //8-Mono-T-1
                $m8T_1_path_one = env('APP_URL') . "sample/product/8-Mono-T-1/1.jpeg";
                $m8T_1_one = date('YmdHis') . "922" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m8T_1_one, file_get_contents($m8T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m8T_1_one);
                $image = Image::make($m8T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m8T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m8T_1_one);

                $m8T_1_path_two = env('APP_URL') . "sample/product/8-Mono-T-1/2.jpeg";
                $m8T_1_two = date('YmdHis') . "923" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m8T_1_two, file_get_contents($m8T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m8T_1_two);
                $image = Image::make($m8T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m8T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m8T_1_two);

                $m8T_1_path_three = env('APP_URL') . "sample/product/8-Mono-T-1/3.jpeg";
                $m8T_1_three = date('YmdHis') . "924" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m8T_1_three, file_get_contents($m8T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m8T_1_three);
                $image = Image::make($m8T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m8T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m8T_1_three);


                //8-Mono-T-2
                $m8T_2_path_one = env('APP_URL') . "sample/product/8-Mono-T-2/1.jpeg";
                $m8T_2_one = date('YmdHis') . "925" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m8T_2_one, file_get_contents($m8T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m8T_2_one);
                $image = Image::make($m8T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m8T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m8T_2_one);

                $m8T_2_path_two = env('APP_URL') . "sample/product/8-Mono-T-2/2.jpeg";
                $m8T_2_two = date('YmdHis') . "926" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m8T_2_two, file_get_contents($m8T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m8T_2_two);
                $image = Image::make($m8T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m8T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m8T_2_two);

                $m8T_2_path_three = env('APP_URL') . "sample/product/8-Mono-T-2/3.jpeg";
                $m8T_2_three = date('YmdHis') . "927" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m8T_2_three, file_get_contents($m8T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m8T_2_three);
                $image = Image::make($m8T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m8T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m8T_2_three);

                //9-Mono-T-1
                $m9T_1_path_one = env('APP_URL') . "sample/product/9-Mono-T-1/1.jpeg";
                $m9T_1_one = date('YmdHis') . "928" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m9T_1_one, file_get_contents($m9T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m9T_1_one);
                $image = Image::make($m9T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m9T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m9T_1_one);

                $m9T_1_path_two = env('APP_URL') . "sample/product/9-Mono-T-1/2.jpeg";
                $m9T_1_two = date('YmdHis') . "929" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m9T_1_two, file_get_contents($m9T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m9T_1_two);
                $image = Image::make($m9T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m9T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m9T_1_two);

                $m9T_1_path_three = env('APP_URL') . "sample/product/9-Mono-T-1/3.jpeg";
                $m9T_1_three = date('YmdHis') . "930" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m9T_1_three, file_get_contents($m9T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m9T_1_three);
                $image = Image::make($m9T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m9T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m9T_1_three);


                //9-Mono-T-2
                $m9T_2_path_one = env('APP_URL') . "sample/product/9-Mono-T-2/1.jpeg";
                $m9T_2_one = date('YmdHis') . "931" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m9T_2_one, file_get_contents($m9T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m9T_2_one);
                $image = Image::make($m9T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m9T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m9T_2_one);

                $m9T_2_path_two = env('APP_URL') . "sample/product/9-Mono-T-2/2.jpeg";
                $m9T_2_two = date('YmdHis') . "932" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m9T_2_two, file_get_contents($m9T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m9T_2_two);
                $image = Image::make($m9T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m9T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m9T_2_two);

                $m9T_2_path_three = env('APP_URL') . "sample/product/9-Mono-T-2/3.jpeg";
                $m9T_2_three = date('YmdHis') . "933" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m9T_2_three, file_get_contents($m9T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m9T_2_three);
                $image = Image::make($m9T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m9T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m9T_2_three);

                //10-Mono-T-3
                $m9T_3_path_one = env('APP_URL') . "sample/product/9-Mono-T-3/1.jpeg";
                $m9T_3_one = date('YmdHis') . "934" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m9T_3_one, file_get_contents($m9T_3_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m9T_3_one);
                $image = Image::make($m9T_3_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m9T_3_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m9T_3_one);

                $m9T_3_path_two = env('APP_URL') . "sample/product/9-Mono-T-3/2.jpeg";
                $m9T_3_two = date('YmdHis') . "935" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m9T_3_two, file_get_contents($m9T_3_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m9T_3_two);
                $image = Image::make($m9T_3_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m9T_3_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m9T_3_two);

                $m9T_3_path_three = env('APP_URL') . "sample/product/9-Mono-T-3/3.jpeg";
                $m9T_3_three = date('YmdHis') . "936" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m9T_3_three, file_get_contents($m9T_3_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m9T_3_three);
                $image = Image::make($m9T_3_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m9T_3_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m9T_3_three);

                //10-Mono-T-1
                $m10T_1_path_one = env('APP_URL') . "sample/product/10-Mono-T-1/1.jpeg";
                $m10T_1_one = date('YmdHis') . "937" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m10T_1_one, file_get_contents($m10T_1_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m10T_1_one);
                $image = Image::make($m10T_1_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m10T_1_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m10T_1_one);

                $m10T_1_path_two = env('APP_URL') . "sample/product/10-Mono-T-1/2.jpeg";
                $m10T_1_two = date('YmdHis') . "938" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m10T_1_two, file_get_contents($m10T_1_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m10T_1_two);
                $image = Image::make($m10T_1_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m10T_1_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m10T_1_two);

                $m10T_1_path_three = env('APP_URL') . "sample/product/10-Mono-T-1/3.jpeg";
                $m10T_1_three = date('YmdHis') . "939" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m10T_1_three, file_get_contents($m10T_1_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m10T_1_three);
                $image = Image::make($m10T_1_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m10T_1_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m10T_1_three);


                //10-Mono-T-2
                $m10T_2_path_one = env('APP_URL') . "sample/product/10-Mono-T-2/1.jpeg";
                $m10T_2_one = date('YmdHis') . "940" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m10T_2_one, file_get_contents($m10T_2_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m10T_2_one);
                $image = Image::make($m10T_2_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m10T_2_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m10T_2_one);

                $m10T_2_path_two = env('APP_URL') . "sample/product/10-Mono-T-2/2.jpeg";
                $m10T_2_two = date('YmdHis') . "941" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m10T_2_two, file_get_contents($m10T_2_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m10T_2_two);
                $image = Image::make($m10T_2_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m10T_2_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m10T_2_two);

                $m10T_2_path_three = env('APP_URL') . "sample/product/10-Mono-T-2/3.jpeg";
                $m10T_2_three = date('YmdHis') . "942" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m10T_2_three, file_get_contents($m10T_2_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m10T_2_three);
                $image = Image::make($m10T_2_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m10T_2_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m10T_2_three);

                //10-Mono-T-3
                $m10T_3_path_one = env('APP_URL') . "sample/product/10-Mono-T-3/1.jpeg";
                $m10T_3_one = date('YmdHis') . "943" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m10T_3_one, file_get_contents($m10T_3_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m10T_3_one);
                $image = Image::make($m10T_3_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m10T_3_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m10T_3_one);

                $m10T_3_path_two = env('APP_URL') . "sample/product/10-Mono-T-3/2.jpeg";
                $m10T_3_two = date('YmdHis') . "944" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m10T_3_two, file_get_contents($m10T_3_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m10T_3_two);
                $image = Image::make($m10T_3_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m10T_3_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m10T_3_two);

                $m10T_3_path_three = env('APP_URL') . "sample/product/10-Mono-T-3/3.jpeg";
                $m10T_3_three = date('YmdHis') . "945" . ".jpg";
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $m10T_3_three, file_get_contents($m10T_3_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $m10T_3_three);
                $image = Image::make($m10T_3_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $m10T_3_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $m10T_3_three);


                $productArr = [
                    [
                        'name' => '8 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel8T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel8T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel8T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel8T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel8T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel8T_1_three,
                        'status' => 1,
                        'user_id' => $tmpId,
                        'company_id' => $tmpId
                    ],
                    [
                        'name' => '8 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel8T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel8T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel8T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel8T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel8T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel8T_2_three,
                        'status' => 1,
                        'user_id' => $tmpId,
                        'company_id' => $tmpId
                    ],
                    [
                        'name' => '8 Panel_T-3',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel8T_3_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel8T_3_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel8T_3_three,
                        'thumb_thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel8T_3_one,
                        'thumb_thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel8T_3_two,
                        'thumb_thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel8T_3_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '9 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel9T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel9T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel9T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel9T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel9T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel9T_1_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    [
                        'name' => '9 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel9T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel9T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel9T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel9T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel9T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel9T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    [
                        'name' => '9 Panel_T-3',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel9T_3_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel9T_3_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel9T_3_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel9T_3_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel9T_3_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel9T_3_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '10 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel10T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel10T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel10T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel10T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel10T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel10T_1_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '10 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel10T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel10T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel10T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel10T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel10T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel10T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '10 Panel_T-3',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel10T_3_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel10T_3_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel10T_3_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel10T_3_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel10T_3_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel10T_3_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '11 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel11T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel11T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel11T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel11T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel11T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel11T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '11 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel11T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel11T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel11T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel11T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel11T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel11T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '12 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel12T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel12T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel12T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel12T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel12T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel12T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '12 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel12T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel12T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel12T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel12T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel12T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel12T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '12 Panel_T-3',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel12T_3_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel12T_3_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel12T_3_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel12T_3_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel12T_3_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel12T_3_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '13 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel13T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel13T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel13T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel13T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel13T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel13T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '13 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel13T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel13T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel13T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel13T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel13T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel13T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '14 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel14T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel14T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel14T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel14T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel14T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel14T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '14 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel14T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel14T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel14T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel14T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel14T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel14T_2_three,
                        'status' => 1,
                        'user_id' => $tmpId,
                        'company_id' => $tmpId
                    ],
                    [
                        'name' => '15 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel15T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel15T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel15T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel15T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel15T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel15T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '15 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel15T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel15T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel15T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel15T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel15T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel15T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '16 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel16T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel16T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel16T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel16T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel16T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel16T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '16 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel16T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel16T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel16T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel16T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel16T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel16T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '17 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel17T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel17T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel17T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel17T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel17T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel17T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '18 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel18T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel18T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel18T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel18T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel18T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel18T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '18 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel18T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel18T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel18T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel18T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel18T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel18T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '21 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel21T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel21T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel21T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel21T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel21T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel21T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '21 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel21T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel21T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel21T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel21T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel21T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel21T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '24 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel24T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel24T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel24T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel24T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel24T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel24T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '24 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel24T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel24T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel24T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel24T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel24T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel24T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '27 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel27T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel27T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel27T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel27T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel27T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel27T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '27 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel27T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel27T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel27T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel27T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel27T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel27T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '29 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel29T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel29T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel29T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel29T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel29T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel29T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '29 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel29T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel29T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel29T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel29T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel29T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel29T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '30 Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel30T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel30T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel30T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel30T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel30T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel30T_1_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '30 Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $panel30T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $panel30T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $panel30T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $panel30T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $panel30T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $panel30T_2_three,
                        'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => 'Commercial Installations',
                        'description' => 'Our Previous Project Installation Photos',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $commercial_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $commercial_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $commercial_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $commercial_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $commercial_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $commercial_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '4 Mono Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m4T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m4T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m4T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m4T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m4T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m4T_1_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '4 Mono Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m4T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m4T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m4T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m4T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m4T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m4T_2_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '6 Mono Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m6T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m6T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m6T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m6T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m6T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m6T_1_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '6 Mono Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m6T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m6T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m6T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m6T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m6T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m6T_2_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '6 Mono Panel_T-3',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m6T_3_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m6T_3_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m6T_3_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m6T_3_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m6T_3_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m6T_3_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '7 Mono Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m7T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m7T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m7T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m7T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m7T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m7T_1_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '7 Mono Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m7T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m7T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m7T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m7T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m7T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m7T_2_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '8 Mono Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m8T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m8T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m8T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m8T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m8T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m8T_1_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '8 Mono Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m8T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m8T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m8T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m8T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m8T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m8T_2_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '9 Mono Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m9T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m9T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m9T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m9T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m9T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m9T_1_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '9 Mono Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m9T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m9T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m9T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m9T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m9T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m9T_2_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '9 Mono Panel_T-3',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m9T_3_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m9T_3_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m9T_3_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m9T_3_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m9T_3_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m9T_3_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '10 Mono Panel_T-1',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m10T_1_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m10T_1_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m10T_1_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m10T_1_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m10T_1_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m10T_1_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '10 Mono Panel_T-2',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m10T_2_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m10T_2_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m10T_2_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m10T_2_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m10T_2_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m10T_2_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                    [
                        'name' => '10 Mono Panel_T-3',
                        'description' => '',
                        'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $m10T_3_one,
                        'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $m10T_3_two,
                        'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $m10T_3_three,
                        'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $m10T_3_one,
                        'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $m10T_3_two,
                        'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $m10T_3_three,
                        'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId
                    ],
                ];
                DB::table('products')->insert($productArr);
            }

            $proposalTemplates = ProposalTemplates::first();
            $newProposalTemplates = $proposalTemplates->replicate();
            $newProposalTemplates->company_id = $tmpId;
            /*if ($userDatas->company_category > 1) {
                $newProposalTemplates->aboutas_content = trim('<h1><strong><span style="font-size:24px">About&nbsp;${companies.company_name}</span></strong></h1>

<p><span style="font-size:11pt"><span style="font-family:Arial"><span style="color:#000000">Welcome to our company profile! This is a sample description that you can change from the template settings. To do so, simply log in to the web portal and navigate to the template settings section. If you&#39;re new here, we recommend checking out the welcome email that we sent you, which includes a video tutorial to help you get started.</span></span></span></p>

<p><span style="font-size:11pt"><span style="font-family:Arial"><span style="color:#000000">You can customize your profile by setting up the theme color and adding photos of your products, items, and customer testimonials, as well as terms and conditions - all of which you can do with just a one-time entry from the web portal. Once you&#39;re all set up, you&#39;ll be able to generate quick estimates within seconds using your mobile phone.</span></span></span></p>

<p><span style="font-size:11pt"><span style="font-family:Arial"><span style="color:#000000">Thank you for choosing Quickest. if you need any kind of support you can always reach out to us on<strong> contact@quickestimate.co</strong></span></span></span></p>');
                $newProposalTemplates->est_customer_notes_details = trim('Payment 100% advance
You can edit the notes');
            }*/
            if ($userDatas->company_category > 1) {
                $newProposalTemplates->aboutas_content = trim('<h1><strong><span style="font-size:24px">About&nbsp;${companies.company_name}</span></strong></h1>

<p><span style="font-size:11pt"><span style="font-family:Arial"><span style="color:#000000">Welcome to our company profile! This is a sample description that you can change from the template settings. To do so, simply log in to the web portal and navigate to the template settings section. If you&#39;re new here, we recommend checking out the welcome email that we sent you, which includes a video tutorial to help you get started.</span></span></span></p>

<p><span style="font-size:11pt"><span style="font-family:Arial"><span style="color:#000000">You can customize your profile by setting up the theme color and adding photos of your products, items, and customer testimonials, as well as terms and conditions - all of which you can do with just a one-time entry from the web portal. Once you&#39;re all set up, you&#39;ll be able to generate quick estimates within seconds using your mobile phone.</span></span></span></p>

<p><span style="font-size:11pt"><span style="font-family:Arial"><span style="color:#000000">Thank you for choosing Quickest. if you need any kind of support you can always reach out to us on<strong> contact@quickestimate.co</strong></span></span></span></p>');
                $newProposalTemplates->est_customer_notes_details = trim('Payment 100% advance
You can edit the notes');
            }
            if ($userDatas->company_category == 1) {
                $newProposalTemplates->est_customer_notes_details = trim('Please Refer Detailed Terms & Condition for payment & Warranty');
                $newProposalTemplates->aboutas_content = trim('<h1><strong><span style="font-size:24px">About&nbsp;${companies.company_name}</span></strong></h1>

<p><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:14.0pt">Vision</span></strong><strong> </strong><br />
<span style="font-size:12.0pt">&quot;Empowering a Sustainable Future with Clean Solar Energy&quot;</span></span></span></p>

<p><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:14.0pt">Mission </span></strong><br />
<span style="font-size:12.0pt">&nbsp;&quot; our mission is to lead the transition to a sustainable and renewable energy future through the widespread adoption of solar power. We are committed to delivering innovative, reliable, and affordable solar solutions that not only reduce our carbon footprint but also provide economic and environmental benefits to our customers and communities. With unwavering dedication to quality, innovation, and customer satisfaction, we aim to make solar energy accessible to all, contributing to a greener planet and a brighter tomorrow.&quot;</span></span></span></p>
');
            }
            $newProposalTemplates->save();

            $lastId = $newProposalTemplates->id;

            $path = 'public/document/' . $tmpId;
            if (!Storage::exists($path)) {
                Storage::makeDirectory($path);
            }
            /*if ($userDatas->company_category == 1) {

                $cover_one = env('APP_URL') . "sample/cover/1.png";
                $filename_cover_one = date('YmdHis') . "106cvr" . ".png";
                Image::make($cover_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_one));

                $cover_two = env('APP_URL') . "sample/cover/2.png";
                $filename_cover_two = date('YmdHis') . "107cvr" . ".png";
                Image::make($cover_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_two));

                $cover_three = env('APP_URL') . "sample/cover/3.png";
                $filename_cover_three = date('YmdHis') . "108cvr" . ".png";
                Image::make($cover_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_three));

                $cover_four = env('APP_URL') . "sample/cover/4.png";
                $filename_cover_four = date('YmdHis') . "109cvr" . ".png";
                Image::make($cover_four)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_four));

                $cover_five = env('APP_URL') . "sample/cover/5.png";
                $filename_cover_five = date('YmdHis') . "110cvr" . ".png";
                Image::make($cover_five)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_five));

                $coverArr = [
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_one, 'cover_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_two, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_three, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_four, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_five, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId]
                ];

                DB::table('proposal_template_cover_photos')->insert($coverArr);

                $aboutus_one = env('APP_URL') . "sample/about-us/1.jpg";
                $filename_aboutus_one = date('YmdHis') . "106aboutus" . ".jpg";
                Image::make($aboutus_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_one));

                $aboutus_two = env('APP_URL') . "sample/about-us/2.jpg";
                $filename_aboutus_two = date('YmdHis') . "107aboutus" . ".jpg";
                Image::make($aboutus_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_two));

                $aboutus_three = env('APP_URL') . "sample/about-us/3.jpg";
                $filename_aboutus_three = date('YmdHis') . "108aboutus" . ".jpg";
                Image::make($aboutus_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_three));

                $aboutus_four = env('APP_URL') . "sample/about-us/4.jpg";
                $filename_aboutus_four = date('YmdHis') . "109aboutus" . ".jpg";
                Image::make($aboutus_four)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_four));

                $aboutus_five = env('APP_URL') . "sample/about-us/5.jpg";
                $filename_aboutus_five = date('YmdHis') . "110aboutus" . ".jpg";
                Image::make($aboutus_five)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_five));

                $aboutusArr = [
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_one, 'about_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_two, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_three, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_four, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_five, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId]
                ];
                DB::table('proposal_template_aboutus_photos')->insert($aboutusArr);
            }

            if ($userDatas->company_category != 1) {
                $path_one = env('APP_URL') . "sample/testimonial/t-4.jpg";
                $filename_one = date('YmdHis') . "106" . ".jpg";
                Image::make($path_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_one));

                $path_two = env('APP_URL') . "sample/testimonial/t-5.jpg";
                $filename_two = date('YmdHis') . "107" . ".jpg";
                Image::make($path_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_two));

                $path_three = env('APP_URL') . "sample/testimonial/t-6.jpg";
                $filename_three = date('YmdHis') . "108" . ".jpg";
                Image::make($path_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_three));
                $testimonialArr = [
                    [
                        'name' => 'Sample Testimonials',
                        'client_name_one' => 'Sofie Matos',
                        'client_name_two' => 'Ben Miller',
                        'client_name_three' => 'Joseph Dickens',
                        'description_one' => 'This is a placeholder for sample testimonials that you can customize and add to your Quickest account from the web portal.',
                        'description_two' => 'With Quickest, you can create as many testimonials as you want and easily select them from the dropdown menu while creating an estimate.',
                        'description_three' => 'Adding testimonials to your estimates can help build trust with your customers and increase your chances of closing a deal.',
                        'rating_one' => 5,
                        'rating_two' => 5,
                        'rating_three' => 5,
                        'image_one' => 'public/uploads/thumbnail/' . $filename_one,
                        'image_two' => 'public/uploads/thumbnail/' . $filename_two,
                        'image_three' => 'public/uploads/thumbnail/' . $filename_three,
                        'status' => 0,
                        'is_default' => 1,
                        'user_id' => $tmpId,
                        'company_id' => $tmpId,
                    ],

                ];
                DB::table('testimonials')->insert($testimonialArr);


                $cover_one = env('APP_URL') . "sample/cover/6.jpg";
                $filename_cover_one = date('YmdHis') . "150defcvr" . ".jpg";
                Image::make($cover_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_one));

                $coverArr = [
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_one, 'cover_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId]
                ];
                DB::table('proposal_template_cover_photos')->insert($coverArr);

                $aboutus_one = env('APP_URL') . "sample/about-us/6.jpg";
                $filename_aboutus_one = date('YmdHis') . "151defaboutus" . ".jpg";
                Image::make($aboutus_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_one));

                $aboutusArr = [
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_one, 'about_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId]
                ];
                DB::table('proposal_template_aboutus_photos')->insert($aboutusArr);

                $itemArr = [
                    ['name' => 'Sample item 1', 'description' => trim('- Item details
- You can write detailed description the item
- You can add or import all your item from the web portal
- Pricing will be fetched automatically
- You can edit the price and descriptions while creating the item
- You can add notes below'), 'item_type' => 'Goods', 'inter_state' => 18, 'intra_state' => 18, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 999, 'sales_flag' => 1, 'status' => 0, 'purchase_flag' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId]
                ];
                DB::table('items')->insert($itemArr);

                $commercial_path_one = env('APP_URL') . "sample/product/sample/1.jpg";
                $commercial_one = rand(1000, 9999) . ".jpg";
                Image::make($commercial_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_one));
                Image::make($commercial_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_one));

                $commercial_path_two = env('APP_URL') . "sample/product/sample/2.jpg";
                $commercial_two = rand(1000, 9999) . ".jpg";
                Image::make($commercial_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_two));
                Image::make($commercial_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_two));

                $commercial_path_three = env('APP_URL') . "sample/product/sample/3.jpg";
                $commercial_three = rand(1000, 9999) . ".jpg";
                Image::make($commercial_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_three));
                Image::make($commercial_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_three));


                $productArr = [
                    ['name' => 'Sample Product', 'image_one' => 'public/uploads/thumbnail/' . $commercial_one, 'image_two' => 'public/uploads/thumbnail/' . $commercial_two, 'image_three' => 'public/uploads/thumbnail/' . $commercial_three, 'thumb_image_one' => 'public/uploads/resize_image/' . $commercial_one, 'thumb_image_two' => 'public/uploads/resize_image/' . $commercial_two, 'thumb_image_three' => 'public/uploads/resize_image/' . $commercial_three, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId,'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
                ];
                DB::table('products')->insert($productArr);
            }*/
            if ($userDatas->company_category == 1) {

                $cover_one = env('APP_URL') . "sample/cover/1.png";
                $filename_cover_one = date('YmdHis') . "106cvr" . ".png";
//                Image::make($cover_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_one));
                Storage::disk('s3')->put("public/".$tmpId."/templates/cover/" . $filename_cover_one, file_get_contents($cover_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/cover/" . $filename_cover_one);

                $cover_two = env('APP_URL') . "sample/cover/2.png";
                $filename_cover_two = date('YmdHis') . "107cvr" . ".png";
//                Image::make($cover_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_two));
                Storage::disk('s3')->put("public/".$tmpId."/templates/cover/" . $filename_cover_two, file_get_contents($cover_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/cover/" . $filename_cover_two);

                $cover_three = env('APP_URL') . "sample/cover/3.png";
                $filename_cover_three = date('YmdHis') . "108cvr" . ".png";
//                Image::make($cover_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_three));
                Storage::disk('s3')->put("public/".$tmpId."/templates/cover/" . $filename_cover_three, file_get_contents($cover_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/cover/" . $filename_cover_three);

                $cover_four = env('APP_URL') . "sample/cover/4.png";
                $filename_cover_four = date('YmdHis') . "109cvr" . ".png";
//                Image::make($cover_four)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_four));
                Storage::disk('s3')->put("public/".$tmpId."/templates/cover/" . $filename_cover_four, file_get_contents($cover_four),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/cover/" . $filename_cover_four);

                $cover_five = env('APP_URL') . "sample/cover/5.png";
                $filename_cover_five = date('YmdHis') . "110cvr" . ".png";
//                Image::make($cover_five)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_five));
                Storage::disk('s3')->put("public/".$tmpId."/templates/cover/" . $filename_cover_five, file_get_contents($cover_five),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/cover/" . $filename_cover_five);

                $coverArr = [
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/'.$tmpId.'/templates/cover/' . $filename_cover_one, 'cover_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/'.$tmpId.'/templates/cover/' . $filename_cover_two, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/'.$tmpId.'/templates/cover/' . $filename_cover_three, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/'.$tmpId.'/templates/cover/' . $filename_cover_four, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/'.$tmpId.'/templates/cover/' . $filename_cover_five, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId]
                ];

                DB::table('proposal_template_cover_photos')->insert($coverArr);

                $aboutus_one = env('APP_URL') . "sample/about-us/1.jpg";
                $filename_aboutus_one = date('YmdHis') . "106aboutus" . ".jpg";
//                Image::make($aboutus_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_one));
                Storage::disk('s3')->put("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_one, file_get_contents($aboutus_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_one);

                $aboutus_two = env('APP_URL') . "sample/about-us/2.jpg";
                $filename_aboutus_two = date('YmdHis') . "107aboutus" . ".jpg";
//                Image::make($aboutus_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_two));
                Storage::disk('s3')->put("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_two, file_get_contents($aboutus_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_two);


                $aboutus_three = env('APP_URL') . "sample/about-us/3.jpg";
                $filename_aboutus_three = date('YmdHis') . "108aboutus" . ".jpg";
//                Image::make($aboutus_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_three));
                Storage::disk('s3')->put("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_three, file_get_contents($aboutus_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_three);

                $aboutus_four = env('APP_URL') . "sample/about-us/4.jpg";
                $filename_aboutus_four = date('YmdHis') . "109aboutus" . ".jpg";
//                Image::make($aboutus_four)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_four));
                Storage::disk('s3')->put("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_four, file_get_contents($aboutus_four),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_four);

                $aboutus_five = env('APP_URL') . "sample/about-us/5.jpg";
                $filename_aboutus_five = date('YmdHis') . "110aboutus" . ".jpg";
//                Image::make($aboutus_five)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_five));
                Storage::disk('s3')->put("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_five, file_get_contents($aboutus_five),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_five);

                $aboutusArr = [
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/'.$tmpId.'/templates/aboutus/' . $filename_aboutus_one, 'about_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/'.$tmpId.'/templates/aboutus/' . $filename_aboutus_two, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/'.$tmpId.'/templates/aboutus/' . $filename_aboutus_three, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/'.$tmpId.'/templates/aboutus/' . $filename_aboutus_four, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/'.$tmpId.'/templates/aboutus/' . $filename_aboutus_five, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId]
                ];
                DB::table('proposal_template_aboutus_photos')->insert($aboutusArr);

                $contentMsgArr = [
                    ['name' => 'Welcome', 'description' => trim('Hello @leadName ji,

*Welcome to ABC Solar Energy Private Limited!*

Thank you for considering us for your solar rooftop power plant needs. At Heaven Solar Energy, we are committed to providing sustainable and efficient solar energy solutions tailored to your unique requirements.

We are proud to highlight:
- *5000+ Rooftop Installations* completed
- *30+ Dedicated Staff* members
- *24-Hour Service Support*

Our expert team ensures seamless installation and exceptional service, helping you harness the power of the sun to reduce energy costs and contribute to a greener future.

For more information, please visit our website: http://Xyz.com

We look forward to partnering with you on your journey to sustainable energy.

Warm regards,
@senderName'), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Follow up 1', 'description' => trim("Hi @leadName

It was great visiting your rooftop and discussing your solar needs. I've sent the quotation to you. Please review it and let me know if you have any questions.

                Thank you!
                @senderName"), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Follow up 2', 'description' => trim("Hello @leadName ji,

Just following up on the quotation I sent a few days ago. Have you had a chance to review it? I'm here to answer any questions you might have.

Looking forward to your feedback.

@senderName"), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Follow up 3', 'description' => trim("Hello @leadName ji,

Hope you’re doing well. I wanted to check in regarding the solar quotation. We’re excited to help you go solar and would love to finalize the details.

Please let me know if there's anything you need.

Best,
@senderName"), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Follow up 4', 'description' => trim("Hello @leadName ji,

I hope all is well. Just a final follow-up on the solar quotation. If you need any adjustments or have any concerns, please let me know. We’re eager to assist you in making the switch to solar energy.

Thank you for considering Heaven Solar Energy.

Best regards,
@senderName"), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => $tmpId, 'company_id' => $tmpId]

                ];
                DB::table('content_messages')->insert($contentMsgArr);

                $termConditionArr1 = [
                    "name" => "Residential BOM & Terms", "description" => '<table align="center" cellspacing="0" style="border-collapse:collapse; width:100%">
	<tbody>
		<tr>
			<td colspan="5" style="border-bottom:none; border-left:1px solid #e7e6e6; border-right:1px solid #e7e6e6; border-top:1px solid #e7e6e6; height:35px; text-align:center; vertical-align:bottom; white-space:nowrap; width:584px"><span style="font-size:27px"><span style="color:black"><span style="font-family:Calibri,sans-serif">Bill of Material&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:1px solid #bbbdc0; height:20px; text-align:center; vertical-align:bottom; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Sr No.</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:1px solid #bbbdc0; text-align:center; vertical-align:bottom; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Item</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:1px solid #bbbdc0; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Qty</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:1px solid #bbbdc0; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Unit</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:1px solid #bbbdc0; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Brand</span></strong></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Solar Panels&nbsp; (PV Modules)</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As specified in Quote</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">2</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Solar String Inverter&nbsp;&nbsp;</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As specified in Quote</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:59px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">3</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Solar 80 micron HDGI Structure*<br />
			60 x 40 mm x 2 mm For Leg , Rafters<br />
			40 x 40 mm x 2 mm For purlins</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As specified in Quote</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:26px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">4</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Protection Devices</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:49px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">4.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">ACDB (IP65) - With SPD, Fuse &amp; MCB<br />
			DCDB (IP65) - With SPD, Fuse &amp; MCB&nbsp;</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">polycab /schineder&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:27px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">5</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Cables</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">&nbsp;<span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">4 SQ MM </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">DC Solar Copper Cable, XLS-R, UV RESISTANT, 1100V Grade, Double Insulated</span></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">30</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5.2</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">4 SQ MM or 6 SQ MM AC Wire , XLS- R</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">30</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5.3</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">4 SQ MM Copper Earthing wire for AC &amp; DC</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">30</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5.4</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">16 SQ MM Aluminium Wire For LA</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">30</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5.5</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">UPVC Conduit Pipe for wiring&nbsp;</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:33px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">6</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Earthing / LA - lightning arrestor</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">6.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">200 Micron Copper Coated 1 Meter Earthing Rod for AC / DC &amp; LA</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">3</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">6.2</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1 Meter copper LA with 3 spike &amp; insulator</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Standard&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">7</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Data Logger&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Wifi Stick : Data Loger for Oniline Monitoring</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As per inverter</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">8</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Other Accessories</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable tie, <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">SS304 </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">300mm (100Pcs/Pkt)</span></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Ss304</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.2</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Ferules &amp; Cable Tags</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Standard</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.3</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Lugs Ring Type As per wiring requirements</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Coper</span></span></span></td>
		</tr>
	</tbody>
</table>

<p>&nbsp;</p>

<div style="page-break-after:always"><span style="display:none">&nbsp;</span></div>

<table border="0" cellpadding="1" cellspacing="0" style="width:100%">
	<tbody>
		<tr>
			<td style="text-align:center"><span style="font-size:18px"><strong>WARRANTY TERMS</strong></span></td>
		</tr>
	</tbody>
</table>

<p><span style="font-size:16px"><strong>General Terms:</strong></span></p>

<ul>
	<li>Material dispatch and Installation shall be started upon DISCOM approval only.</li>
	<li>For better performance, solar panels should be cleaned by customer two times in a week.</li>
	<li>Concealed wiring shall be done by company, if possible only. Otherwise, customer should do concealed wiring with their wiremen where material shall be provided by Company.</li>
	<li>After successful installation, Customer shall take care of solar plant by doing timely cleaning. If we found less generation at the time of attending complaint due to non-cleaning, we may charge you additional service&nbsp; charge.</li>
	<li>There is manufacturing warranty for all electronics equipment. Company will help to claim this warranty if require.&nbsp;</li>
</ul>

<p><span style="font-size:16px"><strong>Goverment Subsidy:</strong></span></p>

<ul>
	<li><strong>Subsidy Credit:</strong> If applicable, any subsidy will be directly credited to the customer&#39;s account. Our company will handle all necessary documentation with the government.</li>
	<li><strong>Delay Disclaimer:</strong> Please note that subsidy amounts may experience delays from the government&#39;s side. Our company is not liable to compensate for any delays or non-receipt of subsidies if not provided by the government.</li>
</ul>

<p><strong>1. Solar Panel (PV Modules) Performance Warranty</strong>:</p>

<ul>
	<li>90% of rated capacity for the first 10 years.</li>
	<li>80% of rated capacity for the next 15 years.</li>
	<li>Total Panel Life: 25 years.</li>
	<li>Refer Solar Panel Datasheet for detailed warranty terms</li>
</ul>

<p><strong>2. Inverter Manufacturing Defect Warranty:</strong></p>

<ul>
	<li>5 years, extendable.</li>
	<li>Refer Inverter Datasheet for detailed warranty terms</li>
</ul>

<p><strong>3. Balance of System (BOS):</strong></p>

<ul>
	<li>Equipment/products supplied by us which are warranted against defects due to poor material, design, or workmanship.</li>
	<li>This warranty is valid for 12 months from the date of commissioning or when put into service, whichever is earlier.</li>
</ul>

<p><strong>4. Operation &amp; Maintenance:</strong></p>

<ul>
	<li>We offer 5 years of O&amp;M support, including fault finding, remote monitoring, site visits, and assistance with warranty claims for components.</li>
	<li>O&amp;M does not include solar panel cleaning and washing.</li>
</ul>

<p><strong>Warranty&nbsp;Notes:</strong></p>

<p>- All warranties provided by the manufacturer/supplier are in favor of the buyer and cover the equipment for the specified period.<br />
- Warranties ensure safe working of individual components and vary in validity period.<br />
- Warranties do not cover damages caused by external hazardous conditions.</p>

<p><strong>Schedule for Site Completion:</strong></p>

<ul>
	<li>Dispatch within 6 weeks after order confirmation with payment.</li>
	<li>The entire power plant will be installed and commissioned within 60-70 days (approx.) after project accreditation, contract agreement, and possession of the site.</li>
</ul>

<p><strong>Payment Terms:</strong></p>

<ul>
	<li>10% advance payment upon contract signing.</li>
	<li>50% upon delivery of structure material.</li>
	<li>30% upon delivery of modules.</li>
	<li>10% before meter installation is complete.</li>
</ul>

<p><strong>Quotation Validity:</strong></p>

<ul>
	<li>1 week from the date of issue.</li>
</ul>

<p><strong>Warranty Exclusions:</strong></p>

<ul>
	<li>The warranty will not cover failures due to:</li>
	<li>Damage or defect caused by transportation, accident, misuse, lack of maintenance, improper usage, or negligence by the owner.</li>
	<li>Wilful damage, normal wear and tear, abuse, or misuse of equipment/product.</li>
	<li>Damage or defect caused by Force Majeure events, including fire, earthquake, flood, or other natural disasters.</li>
	<li>Damage or defect caused by unauthorized alterations, modifications, or conversions.</li>
	<li>Repairs carried out by personnel not authorized by the contractor.</li>
	<li>Defects or damages due to external causes.</li>
	<li>Parts and components repaired or replaced during the warranty period are warranted only for the original warranty period. The contractor will take back replaced or defective material.</li>
</ul>

<p><strong>Scope of Work For Customer:</strong></p>

<ul>
	<li>Providing access/approach to rooftop.</li>
	<li>If any electrical modification is required from DISCOM ( i.e. ELCB, changeover switch etc.) Customer shall provide necessary support.</li>
	<li>Provide necessary documents for project approvals from State/Central Government .</li>
	<li>Site clearance, ladders, water, and electricity supply for smooth installation and commissioning of the project.</li>
	<li>Customer shall provide Safe Place for Material unloading and storage during the work execution</li>
</ul>
', 'user_id' => $tmpId, 'company_id' => $tmpId
                ];
                DB::table('term_conditions')->insertGetId($termConditionArr1);
                $termConditionArr = [
                    "name" => "Commercial BOM & Terms", "description" => '<table align="center" cellspacing="0" style="border-collapse:collapse; width:100%">
	<tbody>
		<tr>
			<td colspan="5" style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:none; border-top:none; height:29px; text-align:center; vertical-align:bottom; white-space:nowrap; width:688px"><span style="font-size:27px"><span style="color:black"><span style="font-family:Calibri,sans-serif">Bill of Material&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Sr No.</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Item</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><strong><span style="color:black"><span style="font-family:Calibri,sans-serif">Qty</span></span></strong></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><strong><span style="color:black"><span style="font-family:Calibri,sans-serif">Unit</span></span></strong></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><strong><span style="color:black"><span style="font-family:Calibri,sans-serif">Brand</span></span></strong></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">PV Module, <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">570W </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">N-type TOPCON Technology</span></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As specified&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1.2</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Solar Inverter Solis&nbsp;</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As specified&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">2</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Module Mounting Structure</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:none; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:116px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">2.1</span></span></span></td>
			<td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">For RCC Tarrace : -Leg,Rafter - 60x40x2mm HDGI Pipe<br />
			-Perlin, Support - 40x40x2mm HDGI Pipe<br />
			-Ss304 Nut Bolting structure<br />
			For Tin Shed:<br />
			Aluminium Mono Rails as per site requirements</span></span></span></td>
			<td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px">Nos</td>
			<td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">Standard</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:27px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">3</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">DC cables</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:46px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">3.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cables 1C X&nbsp; <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">4 SQ MM </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">DC Solar Copper Cable, XLS-R, UV RESISTANT, 1100V Grade, Double Insulated</span></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">500</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:30px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">3.2</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">MC 4 Connector <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">1500Volt , IP65</span></strong></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">20</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Elmex/Sibas</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">4</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">AC Cables</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:46px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">4.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1.1 KV GRADE, <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">3.5C X 25 Sq MM XLPE ALU. ARMOURED </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable (Inverter to ACDB) X (1 Run)</span></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:52px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">4.2</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1.1 KV GRADE, <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">3.5C X&nbsp; 25 Sq MM XLPE ALU. ARMOURED </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable (ACDB to Costumer LT Panel) X (1 Run)</span></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">40</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:23px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">5</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">ACDB</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:none; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:77px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">5.1</span></span></span></td>
			<td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">2 in 1 Out ACDB with MCB/ MCCB/ Fuse / Contactor &amp; RYB indicator with AL busbar or Copper Cable.<br />
			<br />
			For MCB rating please refer to Single Line Diagram</span></span></span></td>
			<td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px">
			<p>Nos</p>
			</td>
			<td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">polycab</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:23px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">6</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">DCDB</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">6.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">04 IN/04 OUT with 8 Nos. of <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">DC fuse</span></strong></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Phoenix</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Conduit Pipe / Cable tray/Walkaway</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">UPVC <span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">pipe /</span></span></span><span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">FRP </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable tray for wiring</span></span></span></span></strong></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Waterway</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7.2</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Walkway for Tin shed (FRP)&nbsp;</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Standard</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7.2</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">T Connector - UPVC</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Waterway</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7.3</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Elbows -UPVC</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Waterway</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">8</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Earthing &amp; LA&nbsp; (Earthing wires)</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">1Cx4 Sq.mm </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">green(Panel to panel earthing)</span></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">100</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.2</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">1Cx10 Sq.mm Copper Wire OR GI strip</span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">25X3mm&nbsp; (inverter </span></span></span><span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">AC earthing &amp; structureearthing</span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">)</span></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">100</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:117px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.3</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Earthing Kit<br />
			50 mm Dia x 3 Meter long HDGI Earthing Rod with Strip<br />
			with Earthing Chemical with 3 Meter deep with RCC Chamber<br />
			Inverter &amp; ACDB Earthing - 1 Nos.<br />
			Structure &amp; DC Earthing - 1 Nos.<br />
			Lightning arrestor - 1 Nos</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">3</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Elink Earthing&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:28px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.4</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable 1CX50 Sq.mm AL Wire Or&nbsp; <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">25x3 GI Strip </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">(L.A. Earthing)</span></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">40</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Hotdip Galvanised</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.5</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">ESE Lightning Arrestor (107 meter Radius)</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Shockpro</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.6</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Insulator for LA in case of GI roof</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Standard</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">9</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Data Logger&nbsp;</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">9.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Wifi Stick : Data Loger for Oniline Monitoring</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As per inverter</span></span></span></td>
		</tr>
		<tr>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">10</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Other Accessories</span></strong></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
			<td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1.3</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable tie, <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">SS304 </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">300mm (100Pcs/Pkt)</span></span></span></span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Ss304</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">10.1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Ferules &amp; Cable Tags</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Standard</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">10.2</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Lugs Ring Type ( 4 Sq mm, 4mm Dia) for panel to panel Earthing</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Coper</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">10.3</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Lugs Ring Type ( 16 Sq mm cable ,10mm Dia)-structure/inverter earthing</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Coper</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">10.4</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Lugs Ring Type ( 35 Sq mm, 10mm Dia) for L.A.</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Coper</span></span></span></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">10.5</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Lugs PIN Type (120 Sq mm,10mm Dia) for inverter</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
			<td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">BI metalic</span></span></span></td>
		</tr>
		<tr>
		</tr>
	</tbody>
</table>

<p>&nbsp;</p>

<div style="page-break-after:always"><span style="display:none">&nbsp;</span></div>

<p style="text-align:center"><span style="font-size:24px"><span style="color:#330099"><strong>WARRANTY TERMS</strong></span></span></p>

<p><strong>1. Solar Panel (PV Modules) Performance Warranty</strong>:</p>

<ul>
	<li>90% of rated capacity for the first 10 years.</li>
	<li>80% of rated capacity for the next 15 years.</li>
	<li>Total Panel Life: 25 years.</li>
	<li>Refer Solar Panel Datasheet for detailed warranty terms</li>
</ul>

<p><strong>2. Inverter Manufacturing Defect Warranty:</strong></p>

<ul>
	<li>5 years, extendable.</li>
	<li>Refer Inverter Datasheet for detailed warranty terms</li>
</ul>

<p><strong>3. Balance of System (BOS):</strong></p>

<ul>
	<li>Equipment/products supplied by us which are warranted against defects due to poor material, design, or workmanship.</li>
	<li>This warranty is valid for 12 months from the date of commissioning or when put into service, whichever is earlier.</li>
</ul>

<p><strong>Operation &amp; Maintenance:</strong></p>

<ul>
	<li>We offer 5 years of O&amp;M support, including fault finding, remote monitoring, site visits, and assistance with warranty claims for components.</li>
	<li>O&amp;M does not include solar panel cleaning and washing.</li>
</ul>

<p><strong>Notes:</strong></p>

<p>- All warranties provided by the manufacturer/supplier are in favor of the buyer and cover the equipment for the specified period.<br />
- Warranties ensure safe working of individual components and vary in validity period.<br />
- Warranties do not cover damages caused by external hazardous conditions.</p>

<p><strong>Schedule for Site Completion:</strong></p>

<ul>
	<li>Dispatch within 6 weeks after order confirmation with payment.</li>
	<li>The entire power plant will be installed and commissioned within 60-70 days (approx.) after project accreditation, contract agreement, and possession of the site.</li>
</ul>

<p><strong>Payment Terms:</strong></p>

<ul>
	<li>10% advance payment upon contract signing.</li>
	<li>50% upon delivery of structure material.</li>
	<li>30% upon delivery of modules.</li>
	<li>10% before meter installation is complete.</li>
</ul>

<p><strong>Quotation Validity:</strong></p>

<ul>
	<li>1 week from the date of issue.</li>
</ul>

<p><strong>Warranty Exclusions:</strong></p>

<ul>
	<li>The warranty will not cover failures due to:</li>
	<li>Damage or defect caused by transportation, accident, misuse, lack of maintenance, improper usage, or negligence by the owner.</li>
	<li>Wilful damage, normal wear and tear, abuse, or misuse of equipment/product.</li>
	<li>Damage or defect caused by Force Majeure events, including fire, earthquake, flood, or other natural disasters.</li>
	<li>Damage or defect caused by unauthorized alterations, modifications, or conversions.</li>
	<li>Repairs carried out by personnel not authorized by the contractor.</li>
	<li>Defects or damages due to external causes.</li>
	<li>Parts and components repaired or replaced during the warranty period are warranted only for the original warranty period. The contractor will take back replaced or defective material.</li>
</ul>

<p><strong>Scope of Work For Customer:</strong></p>

<ul>
	<li>Providing access/approach to rooftop.</li>
	<li>If any electrical modification is required from DISCOM ( i.e. ELCB, changeover switch etc.) Customer shall provide necessary support.</li>
	<li>Provide necessary documents for project approvals from State/Central Government .</li>
	<li>Site clearance, ladders, water, and electricity supply for smooth installation and commissioning of the project.</li>
	<li>Customer shall provide Safe Place for Material unloading and storage during the work execution</li>
</ul>

<p>&nbsp;</p>
', 'user_id' => $tmpId, 'company_id' => $tmpId
                ];
                $term_condition_id = DB::table('term_conditions')->insertGetId($termConditionArr);


                $labelArr = [
                    ['name' => 'Cold Lead', 'color_code' => '#006398', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Dispatch Pending', 'color_code' => '#fa4e64', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Doc Query', 'color_code' => '#fdac64', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Hot Lead', 'color_code' => '#ab408b', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Installation Pending', 'color_code' => '#fa4e64', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Loan Pending', 'color_code' => '#fa4e64', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Meter Pending', 'color_code' => '#fa4e64', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Owner Ref.', 'color_code' => '#43516c', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Payment pending', 'color_code' => '#fa4e64', 'user_id' => $tmpId, 'company_id' => $tmpId],
                ];
                DB::table('lead_groups')->insert($labelArr);
            }

            if ($userDatas->company_category != 1) {
                $path_one = env('APP_URL') . "sample/testimonial/t-4.jpg";
                $filename_one = date('YmdHis') . "106" . ".jpg";
//                Image::make($path_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_one));
                Storage::disk('s3')->put("public/".$tmpId."/testimonials/" . $filename_one, file_get_contents($path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/testimonials/" . $filename_one);

                $path_two = env('APP_URL') . "sample/testimonial/t-5.jpg";
                $filename_two = date('YmdHis') . "107" . ".jpg";
//                Image::make($path_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_two));
                Storage::disk('s3')->put("public/".$tmpId."/testimonials/" . $filename_two, file_get_contents($path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/testimonials/" . $filename_two);

                $path_three = env('APP_URL') . "sample/testimonial/t-6.jpg";
                $filename_three = date('YmdHis') . "108" . ".jpg";
//                Image::make($path_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_three));
                Storage::disk('s3')->put("public/".$tmpId."/testimonials/" . $filename_three, file_get_contents($path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/testimonials/" . $filename_three);
                $testimonialArr = [
                    [
                        'name' => 'Sample Testimonials',
                        'client_name_one' => 'Sofie Matos',
                        'client_name_two' => 'Ben Miller',
                        'client_name_three' => 'Joseph Dickens',
                        'description_one' => 'This is a placeholder for sample testimonials that you can customize and add to your Quickest account from the web portal.',
                        'description_two' => 'With Quickest, you can create as many testimonials as you want and easily select them from the dropdown menu while creating an estimate.',
                        'description_three' => 'Adding testimonials to your estimates can help build trust with your customers and increase your chances of closing a deal.',
                        'rating_one' => 5,
                        'rating_two' => 5,
                        'rating_three' => 5,
                        'image_one' => 'public/'.$tmpId.'/testimonials/' . $filename_one,
                        'image_two' => 'public/'.$tmpId.'/testimonials/' . $filename_two,
                        'image_three' => 'public/'.$tmpId.'/testimonials/' . $filename_three,
                        'status' => 0,
                        'is_default' => 1,
                        'user_id' => $tmpId,
                        'company_id' => $tmpId,
                    ],

                ];
                DB::table('testimonials')->insert($testimonialArr);


                $cover_one = env('APP_URL') . "sample/cover/6.jpg";
                $filename_cover_one = date('YmdHis') . "150defcvr" . ".jpg";
//                Image::make($cover_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_one));
                Storage::disk('s3')->put("public/".$tmpId."/templates/cover/" . $filename_cover_one, file_get_contents($cover_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/cover/" . $filename_cover_one);

                $coverArr = [
                    ['proposal_template_id' => $lastId, 'image_icon' => "public/".$tmpId."/templates/cover/" . $filename_cover_one, 'cover_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId]
                ];
                DB::table('proposal_template_cover_photos')->insert($coverArr);

                $aboutus_one = env('APP_URL') . "sample/about-us/6.jpg";
                $filename_aboutus_one = date('YmdHis') . "151defaboutus" . ".jpg";
//                Image::make($aboutus_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_one));
                Storage::disk('s3')->put("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_one, file_get_contents($aboutus_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/aboutus/" . $filename_aboutus_one);

                $aboutusArr = [
                    ['proposal_template_id' => $lastId, 'image_icon' => 'public/'.$tmpId.'/templates/aboutus/' . $filename_aboutus_one, 'about_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId]
                ];
                DB::table('proposal_template_aboutus_photos')->insert($aboutusArr);


                $path_ones = env('APP_URL') . "sample/image.png";
                $filename_ones = date('YmdHis') . "10654" . ".png";
//                Image::make($path_ones)->save(storage_path("app/public/uploads/items/" . $filename_ones));

                Storage::disk('s3')->put("public/".$tmpId."/items/" . $filename_ones, file_get_contents($path_ones),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/items/" . $filename_ones);
                $itemArr = [
                    ['name' => 'Sample item 1', 'description' => trim('- Item details
- You can write detailed description the item
- You can add or import all your item from the web portal
- Pricing will be fetched automatically
- You can edit the price and descriptions while creating the item
- You can add notes below'), 'item_type' => 'Goods', 'inter_state' => 18, 'intra_state' => 18, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 999, 'sales_flag' => 1, 'status' => 0, 'purchase_flag' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId, 'image_icon' => "public/".$tmpId."/items/" . $filename_ones]
                ];
                DB::table('items')->insert($itemArr);

                $commercial_path_one = env('APP_URL') . "sample/product/sample/1.jpg";
                $commercial_one = rand(1000, 9999) . ".jpg";
//                Image::make($commercial_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_one));
//                Image::make($commercial_path_one)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_one));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $commercial_one, file_get_contents($commercial_path_one),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $commercial_one);
                $image = Image::make($commercial_path_one)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $commercial_one, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $commercial_one);

                $commercial_path_two = env('APP_URL') . "sample/product/sample/2.jpg";
                $commercial_two = rand(1000, 9999) . ".jpg";
//                Image::make($commercial_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_two));
//                Image::make($commercial_path_two)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_two));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $commercial_two, file_get_contents($commercial_path_two),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $commercial_two);
                $image = Image::make($commercial_path_two)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $commercial_two, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $commercial_two);

                $commercial_path_three = env('APP_URL') . "sample/product/sample/3.jpg";
                $commercial_three = rand(1000, 9999) . ".jpg";
//                Image::make($commercial_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_three));
//                Image::make($commercial_path_three)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . $commercial_three));
                Storage::disk('s3')->put("public/".$tmpId."/products/thumbnail/" . $commercial_three, file_get_contents($commercial_path_three),'public');
                $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/products/thumbnail/" . $commercial_three);
                $image = Image::make($commercial_path_three)->resize(64, 64);
                Storage::disk('s3')->put("public/".$tmpId."/products/resize_image/" . $commercial_three, $image->stream()->__toString(),'public');
                $publicUrlThumbnail = Storage::disk('s3')->url("public/".$tmpId."/products/resize_image/" . $commercial_three);


                $productArr = [
                    ['name' => 'Sample Product', 'image_one' => 'public/'.$tmpId.'/products/thumbnail/' . $commercial_one, 'image_two' => 'public/'.$tmpId.'/products/thumbnail/' . $commercial_two, 'image_three' => 'public/'.$tmpId.'/products/thumbnail/' . $commercial_three,'thumb_image_one' => 'public/'.$tmpId.'/products/resize_image/' . $commercial_one, 'thumb_image_two' => 'public/'.$tmpId.'/products/resize_image/' . $commercial_two, 'thumb_image_three' => 'public/'.$tmpId.'/products/resize_image/' . $commercial_three, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
                ];
                DB::table('products')->insert($productArr);

                $contentMsgArr = [
                    ['name' => 'Quickest Message', 'description' => trim('HI @leadName,
Hope you are well. I have just installed the Quickest app, it helps to send professional proposals within a few seconds. It helps to increase the sales and manage all my leads from my phone.
Sign up for free here: Quickestimate.co

Thank you
@senderName'), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => $tmpId, 'company_id' => $tmpId]
                ];
                DB::table('content_messages')->insert($contentMsgArr);

                $termConditionArr = [
                    "name" => "Basic Terms", "description" => '<p><span style="color:#3498db"><span style="font-size:16px"><strong>Terms &amp; Condition</strong></span></span></p>
                <p>Material dispatch and Installation shall be started upon DISCOM approval only.</p>
                <p>For better performance, solar panels should be cleaned by customer two times in a week.</p>
                <p>Concealed wiring shall be done by company, if possible only. Otherwise, customer should do concealed wiring with their wiremen where material shall be provided by Company.</p>
                <p>After successful installation, Customer shall take care of solar plant by doing timely cleaning. If we found less generation at the time of attending complaint due to non-cleaning, we may charge you additional service&nbsp; charge.</p>
                <p>There is manufacturing warranty for all electronics equipment. Company will help to claim this warranty if require.&nbsp;</p>
                <p>The company will provide up to 30 meter wire 25 Year warranty of PV Module, 10 Year warranty of Inverter and 5 Year O&amp;M of System by Company</p>
                <p><span style="color:#3498db"><span style="font-size:16px"><strong>Scope of Work For Customer:</strong></span></span></p>
                <p>Providing access/approach to rooftop&nbsp;</p>
                <p>If any system modification is required from DISCOM ( i.e. ELCB, changeover etc.)&nbsp;</p>
                <p>Provide necessary documents for project approvals from State/Central Government&nbsp;</p>
                <p>Site clearance, water, and electricity for smooth installation and commissioning of the project&nbsp;</p>
                <p>Required civil work and approvals to complete the project within the timeline proposed</p>
                <p>Safe storage of materials (PV modules, Inverter, etc.) upon delivery&nbsp;</p>
                <p>Customer shall provide Safe Place for Material unloading and storage during the work execution</p>
                <p><span style="color:#3498db"><span style="font-size:16px"><strong>Warranty Exclusion:</strong></span></span></p>
                <p>Damage due to improper handling&nbsp;</p>
                <p>In absence of full payment&nbsp;</p>
                <p>Damage to due to force majeure Defects due to third party inference (direct or indirect) or act to our system&nbsp;</p>
                <p>This offer in itself or any subsequent Communications/documents will be subject to standard Force Majeure conditions.&nbsp;</p>
                <p>Jurisdiction: Subject to Surat jurisdiction.</p>', 'user_id' => $tmpId, 'company_id' => $tmpId
                ];
                $term_condition_id = DB::table('term_conditions')->insertGetId($termConditionArr);

                $labelArr = [
                    ['name' => 'Hot Lead', 'color_code' => '#fa4e64', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Cold Lead', 'color_code' => '#13a764', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Close won', 'color_code' => '#3d7a44', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Agreed to Buy', 'color_code' => '#ab408b', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Need Support', 'color_code' => '#006398', 'user_id' => $tmpId, 'company_id' => $tmpId],
                    ['name' => 'Qualified for Future', 'color_code' => '#ab4040', 'user_id' => $tmpId, 'company_id' => $tmpId],
                ];
                DB::table('lead_groups')->insert($labelArr);
            }

            $usersPermissionsArr = [
                ['permission_id' => 78, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['permission_id' => 77, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['permission_id' => 76, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['permission_id' => 75, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['permission_id' => 74, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['permission_id' => 73, 'user_id' => $tmpId, 'company_id' => $tmpId],
                ['permission_id' => 70, 'user_id' => $tmpId, 'company_id' => $tmpId]
            ];
            DB::table('users_permissions')->insert($usersPermissionsArr);
            $contentFileArr = [
                ['name' => 'Quickest Broucher ', 'path' => 'template/quickest-broucher.pdf', 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
            ];
            DB::table('content_files')->insert($contentFileArr);
            $path_ones = env('APP_URL') . "sample/signature.png";
            $filename_onesa = date('YmdHis') . "1065474" . ".png";
//            Image::make($path_ones)->save(storage_path("app/public/template/" . $filename_onesa));
            Storage::disk('s3')->put("public/".$tmpId."/templates/signature/" . $filename_onesa, file_get_contents($path_ones),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/".$tmpId."/templates/signature/" . $filename_onesa);

//            $path_ones = env('APP_URL') . "sample/signature.png";
//            $filename_onesa = date('YmdHis') . "1065474" . ".png";
//            Image::make($path_ones)->save(storage_path("app/public/template/" . $filename_onesa));
            /*ProposalTemplates::where('company_id', $tmpId)->update(array('term_condition_id' => $term_condition_id, 'cover_img' => 'public/uploads/thumbnail/' . $filename_cover_one, 'aboutas_img' => 'public/uploads/thumbnail/' . $filename_aboutus_one,'est_signature_img' => 'public/template/' . $filename_onesa));*/

            ProposalTemplates::where('company_id', $tmpId)->update(array('term_condition_id' => $term_condition_id, 'cover_img' => "public/".$tmpId."/templates/cover/" . $filename_cover_one, 'aboutas_img' => "public/".$tmpId."/templates/aboutus/" . $filename_aboutus_one, 'est_signature_img' => "public/".$tmpId."/templates/signature/" . $filename_onesa));
        }

        \Mail::to($request->email)->send(new \App\Mail\ClientUserMail(["name" => $user->name]));
        return $this->sendResponse([], 'Profile Updated!');
    }

    public function postResetPassword(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();
        $validator = \Illuminate\Support\Facades\Validator::make($input, [
            'current_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error', ['error' => $validator->errors()->all()], 400);
        }
        if (!Hash::check($input['current_password'], $user->getAuthPassword())) {
            return $this->sendError('Current password wrong!', ['error' => 'Current password wrong!'], 400);
        }

        User::find($user->id)->update(['password' => Hash::make($input['new_password'])]);
        return $this->sendResponse([], 'Password Updated!');
    }

    public function postUserAccount(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();
        $validator = \Illuminate\Support\Facades\Validator::make($input, [
            'name' => 'required',
            //            'company_name' => 'required',
            'mobile_no' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error', ['error' => $validator->errors()->all()], 400);
        }

        /*   if($request->hasFile('profile_icon')){
               $request->validate([
                   'profile_icon' => 'image|mimes:jpeg,png,jpg|max:1024',
               ]);
               if(Storage::exists($user->profile_icon))
               {
                   Storage::delete($user->profile_icon);
               }

               $path = $request->file('profile_icon')->store('public/profile');
               $input['profile_icon'] = $path;
           }*/


        //        $input['status'] = 'Pending';

        User::find($user->id)->update($input);

        return $this->sendResponse([], 'Acoount setting Updated!');
    }

    public function postUpdateDeviceKey(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();
        /* $validator = \Illuminate\Support\Facades\Validator::make($input, [
             'mobile_device_key' => 'required',
         ]);

         if ($validator->fails()) {
             return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
         }*/
        User::find($user->id)->update(["mobile_device_key" => $input['mobile_device_key']]);
        return $this->sendResponse([], 'Device token Updated!');
    }

    public function getSubscriptionDetail()
    {
        $user = Auth::user();
        $companyId = ($user->company_id) ? $user->company_id : $user->id;
        $fetchUser = User::where('id', $companyId)->select(['plan_end_date', 'plan_start_date', 'created_at', 'popupStatus','follow_up_note_req_flg'])->first();
        $remaining_days = \Carbon\Carbon::parse($fetchUser->plan_start_date)->diffInDays();

        $totalExp = \Carbon\Carbon::parse($fetchUser->plan_start_date)->diffInDays(\Carbon\Carbon::parse($fetchUser->plan_end_date));
        $progress = 0;
        if ($totalExp > 0)
            $progress = number_format(($remaining_days / $totalExp) * 100, 0);
        $id = isset($user->company_id) ? $user->company_id : $user->id;
        $estimateCount = Estimate::where('company_id', $id)->whereMonth('created_at', Carbon::now()->month)->count();
        $userCount = User::where('company_id', $id)->count();
        $plan = PlanHistory::where([['user_id', $id], ['status', 1]])->first();
        $data['popupStatus'] = $fetchUser->popupStatus;
        $data['follow_up_note_req_flg'] = $fetchUser->follow_up_note_req_flg;
        $data['remaining_days'] = $remaining_days;
        $data['total_days'] = $totalExp;
        $data['day_percentage'] = $progress;
        $data['plan_start_date'] = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $fetchUser->plan_start_date)
            ->format('Y-m-d H:i:s');
        $data['plan_end_date'] = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $fetchUser->plan_end_date)
            ->format('Y-m-d H:i:s');
        $data['created_at'] = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $fetchUser->created_at)
            ->format('Y-m-d');
        $data['current_plan'] = $plan;
        $data['total_users'] = $userCount;
        $data['total_estimate'] = $estimateCount;
        return $this->sendResponse($data, 'Subscription plan details!');
    }

    public function userDelete(Request $request)
    {

        $input = $request->all();

        $user = Auth::user();
        $validator = \Illuminate\Support\Facades\Validator::make($input, [
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error', ['error' => $validator->errors()->all()], 400);
        }
        if (!Hash::check($input['password'], $user->getAuthPassword())) {
            return $this->sendError('Enter password wrong!', ['error' => 'Enter password wrong!'], 400);
        }

        User::destroy($user->id);
        return $this->sendResponse([], 'Subscription plan details!');
    }

    public function planExtend()
    {
        $user = Auth::user();
        $plan = Plans::where('isDefault', 1)->first();
        $start_date = Carbon::now();
        $end_date = Carbon::now()->addDays(7);
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        User::where('id', $company_id)->update([
            'popupStatus' => 1,
            'plan_start_date' => $start_date,
            'plan_end_date' => $end_date,
            'remaining_days' => 7,
            'plan_id' => $plan->id,
        ]);
        PlanHistory::where([['user_id', $company_id], ['status', 1]])->update(['status' => 0]);
        PlanHistory::create([
            'user_id' => $company_id,
            'plan_id' => $plan->id,
            'user_limit' => $plan->users_limit,
            'estimate_limit' => $plan->estimate_limit,
            'status' => 1,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
        $mail_details = [];
        $mail_details['user'] = $user;
        $mail_details['plan'] = $plan;
        $user = User::where('id', $company_id)->first();
        \Mail::to($user->email)->send(new \App\Mail\ExtendedMail($mail_details));


        return $this->sendResponse($user, 'Plan Updated!');
    }

    public function destroy(Request $request)
    {
        $input = $request->all();
        $validator = \Illuminate\Support\Facades\Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $teams = User::query()->where('id', $input['id'])->delete();

        return $this->sendResponse([], 'User Deleted!');
    }

    public function userStatus(Request $request)
    {
        $input = $request->all();

        $validator = \Illuminate\Support\Facades\Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $id = $input['id'];

        if (!User::where('id', $id)->first()) {
            return response()->json(['success' => 'User exists!'], 422);
        }
        User::where('id', $id)->update(["invite_status" => $input['status']]);

        DB::table('personal_access_tokens')->where('tokenable_id', $input['id'])->delete();
        DB::table('sessions')->where('user_id', $input['id'])->delete();
        return $this->sendResponse([], 'User status updated');

    }

    public function sendOtpDelete()
    {
        $users = Auth::user();
        if (!$users) {
            return $this->sendError('This Email Address is not exits...', ['error' => 'This Email Address is not exits...'], 402);
        }
        if ($users->invite_status == 0 || $users->invite_status == 2) {
            return $this->sendError('Inactivated', ['error' => 'Your account is deactivated , please contact your admin.'], 402);
        }
        $otp = rand(100000, 999999);

        $user = User::where('email', '=', $users->email)->update(['otp' => $otp]);

        if ($user) {
            $mail_details = [
                'subject' => 'Your OTP is Delete Account',
                'body' => $otp,
                'delete_account_flag'=>1
            ];

            \Mail::to($users->email)->send(new \App\Mail\SendOtpMail($mail_details));
            return response(["status" => 200, 'message' => 'OTP sent successfully']);
        } else {
            return response(["status" => 400, 'message' => 'Invalid']);
        }
    }

    public function verifyOtpDelete(Request $request)
    {
        $user = User::where([['email', '=', $request->email], ['otp', '=', $request->otp]])->first();
        if ($user) {
            User::query()->where('email', $request->email)->delete();
            User::where('company_id', $user->id)->delete();
            return $this->sendResponse([], 'Removed account successfully');
        } else {
            return $this->sendError('Invalid', ['error' => 'Invalid'], 401);
        }
    }

    public function postUserProfile(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $validator = \Illuminate\Support\Facades\Validator::make($input, [
            'profile_icon' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', ['error' => $validator->errors()->all()], 400);
        }

           if($request->hasFile('profile_icon')){
               /*$request->validate([
                   'profile_icon' => 'image|mimes:jpeg,png,jpg|max:1024',
               ]);*/
               if(Storage::exists($user->profile_icon))
               {
//                   Storage::delete($user->profile_icon);
                   Storage::disk('s3')->delete($user->profile_icon);
               }

               /*$path = $request->file('profile_icon')->store('public/profile');
               $input['profile_icon'] = $path;*/
               $path = Storage::disk('s3')->put('public/'.$company_id.'/profile', $request->profile_icon,'public');
               $input['profile_icon'] = 'public/'.$company_id.'/profile/'.basename(Storage::disk('s3')->url($path));
           }
        User::find($user->id)->update(['profile_icon'=>$input['profile_icon']]);

        return $this->sendResponse([], 'Acoount setting Updated!');
    }


    public function storeNew(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'mobile_no' => 'required',

        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 200);
        }

//        try {

        $input = $request->all();

        $id = $input['id'];

        $company_id = (Auth::user()->company_id) ? Auth::user()->company_id : Auth::user()->id;

        if (User::query()->where('email', '=', $input['email'])->where(function ($query) use ($company_id, $id) {
            $query->Where(function ($query) use ($company_id, $id) {
//                    $query->where('company_id', $company_id);
                if ($id != 0) {
                    $query->where('id', '!=', $id);
                }
            });
        })->first()) {
            return $this->sendError('Team member exists!', ['error' => 'Team member exists!'], 200);
        }

        foreach ($input['data'] as $key => $val) {
            if ($val['permission_id'] == 0)
                unset($input['data'][$key]);
        }
        if ($id == 0) {
            //                \DB::enableQueryLog();
            $users = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'mobile_no' => $input['mobile_no'],
                'role_name' => $input['role_name'],
                'company_id' => $company_id,
                'user_role' => "1",
                'permissions' => null,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'status' => 'Approved',
                'is_owner' => 0,
                'customer_show_flg' => 1
            ]);
            //                dd(\DB::getQueryLog($users));
           $insert_id = $users->id;
            if (!empty($input['data'])) {
                $input['data'] = array_map(function ($arr) use ($insert_id, $company_id) {
                    return $arr + ['user_id' => $insert_id, 'company_id' => $company_id];
                }, $input['data']);

                $permission = UserPermission::query()->insert($input['data']);
            }

            $dashboardSettingArr = [
                ['permission_id' => 1, 'user_id' => $insert_id, 'company_id' => $company_id, 'is_primary' =>0],
                ['permission_id' => 2, 'user_id' => $insert_id, 'company_id' => $company_id, 'is_primary' =>1],
//                ['permission_id' => 3, 'user_id' => $insert_id, 'company_id' => $company_id', 'is_primary' =>0],
                ['permission_id' => 4, 'user_id' => $insert_id, 'company_id' => $company_id, 'is_primary' =>1],
                ['permission_id' => 5, 'user_id' => $insert_id, 'company_id' => $company_id, 'is_primary' =>0],
                ['permission_id' => 6, 'user_id' => $insert_id, 'company_id' => $company_id, 'is_primary' =>1],
            ];
            \Illuminate\Support\Facades\DB::table('dashboard_settings')->insert($dashboardSettingArr);
            $users->invite_user_name = Auth::user()->name;
            $mail_details = $users;
            \Mail::to($users->email)->send(new \App\Mail\InviteMail($mail_details));
        } else {

            $update = User::find($id)->update([
                'name' => $request->name,
                'email' => $request->email,
                'mobile_no' => $request->mobile_no,
                'role_name' => $request->role_name,
//                'company_id' => $company_id,
                'user_role' => "1",
                'permissions' => null,
                'customer_show_flg' => 1
            ]);

            /*echo "<pre>";
            print_r($input['data']); die;*/
            if (!empty($input['data'])) {


                $input['data'] = array_map(function ($arr) use ($id, $company_id) {
                    return $arr + ['user_id' => $id, 'company_id' => $company_id];
                }, $input['data']);
                UserPermission::query()->where('user_id', $id)->delete();

                $permission = UserPermission::query()->insert($input['data']);
            } else {
                UserPermission::query()->where('user_id', $id)->delete();
            }
        }
        return $this->sendResponse([], 'User status updated');
//        } catch (\Exception $e) {
//            $bug = $e->getMessage();
//            return redirect()->back()->with('error', $bug);
//        }
    }

    public function editNew($id)
    {
        $data['user'] = User::find($id);
        $company_id = (Auth::user()->company_id) ? Auth::user()->company_id : Auth::user()->id;

        $data['user_permissions'] = UserPermission::query()->where('user_id', $id)->where('company_id', $company_id)->pluck('permission_id')->toArray();

        return $this->sendResponse($data, 'User signed in');
    }

    public function resentMail(Request $request)
    {
        $id = $request->id;
        $users = User::where('id', $id)->first();
        $users->invite_user_name = Auth::user()->name;
        $mail_details = $users;
        \Mail::to($users->email)->send(new \App\Mail\InviteMail($mail_details));
        return $this->sendResponse([], 'Mail Resend Successfully!');
    }

    public function userList($status)
    {
            $user = Auth::user();
            $company_id = ($user->company_id) ? $user->company_id : $user->id;


            $records = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where('invite_status', $status)
                ->select(['id', 'name', 'mobile_no', 'email', 'role_name', 'invite_status','company_id','profile_icon'])
                ->orderBy('name', 'asc')
                ->get();
            $data = array();
            $i = 0;
            foreach ($records as $record) {
                $id = $record->id;
                $role_name = $record->role_name;
                $name = $record->name;
                $email = $record->email;
                $mobile_no = $record->mobile_no;
                $status = $record->invite_status;
                $company_id = $record->company_id;
                if ($record->profile_icon == null) {
                    $profile_icon = null;
                } else {
                    $profile_icon = Storage::disk('s3')->temporaryUrl($record->profile_icon,Carbon::now()->addMinutes(20));
                }

                $data[] = array(
                    "id" => $id,
                    "role_name" => $role_name,
                    "name" => $name,
                    "email" => $email,
                    "mobile_no" => $mobile_no,
                    "status" => $status,
                    "company_id" => $company_id,
                    "profile_icon" => $profile_icon,
                );
            }

        return $this->sendResponse($data, 'User list successfully');
    }
}

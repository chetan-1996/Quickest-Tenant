<?php

namespace App\Http\Controllers\API\V7;

use App\Http\Controllers\API\V7\BaseController as BaseController;
use App\Models\User;
use App\Models\Tenant;
use App\Models\VerificationCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Crypt, DB, Hash, Storage};
use Validator;
use Auth;
use Image;
use LogActivity;

class AuthController extends BaseController
{
    public function __construct()
    {
        $this->username = $this->findUsername();
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

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users'
            // 'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 200);
            //            return response()->json($validator->errors());
        }
        $start_date = date('Y-m-d H:i:s');
        $from_date = date('Y-m-d H:i:s', strtotime("+7 day", strtotime($start_date)));
        $plan = Plans::where('isDefault', 1)->first();
        $user = User::create([
            'is_owner' => $request->is_owner,
            'name' => $request->name,
            'email' => $request->email,
            'company_name' => $request->company_name,
            'plan_start_date' => $start_date,
            'plan_end_date' => $from_date,
            'plan_id' => $plan->id,
            'status' => 'New',
            'invite_status' => 1,
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
}

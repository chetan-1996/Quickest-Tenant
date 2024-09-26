<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\admin\Plans;
use App\Models\PlanHistory;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\admin\CompanyCategory;
use App\Models\VerificationCode;
use App\Mail\SendOtpMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Auth;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $countries = Country::select(["name", "id", "phonecode","sortname","currency_name","currency_code","currency_symbol"])->where('status', '=', 0)->orderBy('name','ASC')->get();
        $company_categories = CompanyCategory::select(["name", "id"])->where('status', '=', 0)->get();
        $segment = '';
        return view('register.signup', compact('segment', 'countries', 'company_categories'));
    }

    public function getState(Request $request)
    {
        $data['states'] = State::where("country_id", $request->country_id)->where('status', '=', 0)
            ->get(["name", "id"]);
        return response()->json($data);
    }

    public function getCity(Request $request)
    {
        $data['cities'] = City::where("state_id", $request->state_id)->where('status', '=', 0)
            ->get(["name", "id"]);
        return response()->json($data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants|max:255',
            'company_name' => 'required|string|max:255',
            'mobile_no' => 'required',
            'country_id' => 'required',
            'company_category' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $start_date = date('Y-m-d H:i:s');
        $from_date = date('Y-m-d H:i:s', strtotime("+7 day", strtotime($start_date)));
        $plan = Plans::where('isDefault', 1)->first();

        $input = $request->all();
        $cleanCompanyName =str_replace(' ', '', preg_replace('/[^a-zA-Z0-9\s]/ ', '', $input['company_name']));
        $baseDomain = Str::slug($cleanCompanyName);
        $generateDomainName = $this->generateDomainName($baseDomain);
        $tenant = Tenant::query()->create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($cleanCompanyName.'@12345678'),
            'mobile_no' => $input['mobile_no'],
            'country_id' => $input['country_id'],
            'state_id' => $input['state_id'],
            'company_name' => $input['company_name'],
            'company_category' => $input['company_category'],
            'domain' => $generateDomainName,
            'plan_start_date' => $start_date,
            'plan_end_date' => $from_date,
            'plan_id' => $plan->id,
            'status' => 'New',
            'invite_status' => 1,
        ]);

        PlanHistory::create([
            'user_id' => $tenant->id,
            'plan_id' => $plan->id,
            // 'user_limit' => $plan->users_limit - 1,
            'user_limit' => $plan->users_limit,
            'estimate_limit' => $plan->estimate_limit,
            'status' => 1,
            'start_date' => $start_date,
            'end_date' => $from_date
        ]);

        $tenant->domains()->create([
            'domain' => $generateDomainName
        ]);
        
        $verificationCode = $this->generateOtp($input['email']);
        $message = "Successfully sent OTP";
        
        $tenantPath = $generateDomainName.'/otp/verification/'.\Crypt::encrypt($verificationCode->user_id).'/'.\Crypt::encrypt($input['email']);
        return redirect()->to($tenantPath)->with('success', $message);
    }

    public function generateDomainName($baseDomain)
    {

        $domain = Str::lower($baseDomain);

        // Check if the domain exists, and if so, append a counter to make it unique
        while (Tenant::where('domain', $domain)->exists()) {
            $counter = rand(1000, 9999);
            $domain = $baseDomain.$counter;
        }
        return $domain;
    }

    public function generateOtp($email)
    {
        $user = Tenant::where('email', $email)->first();

        # User Does not Have Any Existing OTP
        $verificationCode = VerificationCode::where('user_id', $user->id)->latest()->first();

        $now = Carbon::now();

        if ($verificationCode && $now->isBefore($verificationCode->expire_at)) {
            VerificationCode::where('id', $verificationCode->id)->update([
                'otp' => $verificationCode->otp
            ]);
            $updatedverificationCode = VerificationCode::where('user_id', $user->id)->latest()->first();
            return $updatedverificationCode;
        }

        // Create a New OTP

        if($email == 'demo@quickestimate.co'){
            $tmpOtp = "123456";
        }else{
            $tmpOtp = rand(123456, 999999);
        }
        return VerificationCode::create([
            'user_id' => $user->id,
            'otp' => $tmpOtp,
            'expire_at' => Carbon::now()->addMinutes(10)
        ]);
    }

    public function generate(Request $request)
    {

        # Validate Data
        $request->validate([
            'email' => 'required|exists:tenants,email'
        ]);
        $user = Tenant::query()->where('email', $request->email)->first();

        # User Does not Have Any Existing OTP
        $verificationCode = VerificationCode::query()->where('user_id', $user->id)->latest()->first();

        $now = Carbon::now();

        # Generate An OTP
        $verificationCode = $this->generateOtp($request->email);
        //        $message = "Your OTP To Login is - " . $verificationCode->otp;
        $message = "Successfully sent OTP";

        $mail_details = [
            'subject' => 'Your OTP is '.$verificationCode->otp.' for Login to '.config('app.name', 'Laravel'),
            'body' => $verificationCode->otp
        ];
       
        \Mail::to($request->email)->send(new SendOtpMail($mail_details));
        # Return With OTP
        return redirect()->route('otp.verification', [
            'user_id' => \Crypt::encrypt($verificationCode->user_id), 'email' => \Crypt::encrypt($request->email)
        ])
            ->with('success', $message);
    }
}
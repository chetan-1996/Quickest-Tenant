<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VerificationCode;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\DB;
use Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $segment = '';
        return view('login.login', compact('segment'));
    }

    // Generate OTP
    public function generate(Request $request) {

        
         # Validate Data
         $request->validate([
            'email' => 'required|exists:tenants,email'
        ]);
        $user = Tenant::query()->where('email', $request->email)->first();

        # User Does not Have Any Existing OTP
        $verificationCode = VerificationCode::query()->where('user_id', $user->id)->latest()->first();

        $now = Carbon::now();

        $verificationCode = $this->generateOtp($request->email);
        //        $message = "Your OTP To Login is - " . $verificationCode->otp;
        $message = "Successfully sent OTP";

        $mail_details = [
            'subject' => 'Your OTP is '.$verificationCode->otp.' for Login to '.config('app.name', 'Laravel'),
            'body' => $verificationCode->otp
        ];
       
        //\Mail::to($request->email)->send(new SendOtpMail($mail_details));
        // return redirect()->route('otp.verification', 
        //     [ 'user_id' => \Crypt::encrypt($verificationCode->user_id), 
        //     'email' => \Crypt::encrypt($request->email),
        //     'segment' => \Crypt::encrypt($user->domain)]
        // )->with('success', $message);
        $tenantPath = $user->domain.'/otp/verification/'.\Crypt::encrypt($verificationCode->user_id).'/'.\Crypt::encrypt($request->email);
        return redirect()->to($tenantPath)->with('success', $message);
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

    public function verification($user_id)
    {
        $user = Tenant::query()->where('id', \Crypt::decrypt($user_id))->first();
        $time = VerificationCode::where('user_id', \Crypt::decrypt($user_id))
            ->latest()
            ->first();

        $specificDateTime = Carbon::parse($time->updated_at)->addMinutes(1);
        $second = $specificDateTime->diffInSeconds(Carbon::now());
        $isGreaterThanSpecificDateTime = Carbon::now()->gt($specificDateTime);
        
        return view('login.otp-verification')->with([
            'segment' => \Crypt::encrypt($user->domain),
            'user_id' => $user_id,
            'second' => $second, 'isGreaterThanSpecificDateTime' => $isGreaterThanSpecificDateTime
        ]);
    }

    public function loginWithOtp(Request $request)
    {

        #Validation
        $request->validate([
            'user_id' => 'required|exists:tenants,id',
            'otp' => 'required'
        ]);
        $otpInput = implode('', $request->otp);

        #Validation Logic
        $verificationCode = VerificationCode::where('user_id', $request->user_id)->where('otp', $otpInput)->orderby('id','desc')->first();
        
        $now = Carbon::now();
        if (!$verificationCode) {
            return redirect()->back()->with('error', 'Your OTP is not correct');
        } elseif ($verificationCode && $now->isAfter($verificationCode->expire_at)) {
            return redirect()->route('login')->with('error', 'Your OTP has been expired');
        }

        $user = Tenant::whereId($request->user_id)->first();
        // $roleData = DB::table('roles')->where('id', '=', $user->role_id)->select('name as role_name')->first();
        $role_name = "Founder";

        if ($user) {
            // Expire The OTP
            $verificationCode->update([
                'expire_at' => Carbon::now()

            ]);
            if (is_null($user->email_verified_at)) {
                Tenant::whereId($request->user_id)->update(['email_verified_at' => Carbon::now()]);
                $user = Tenant::whereId($request->user_id)->first();
            }//dd($user);
            Auth::guard('tenant')->login($user);
            return redirect()->route('tenant.dashboard', ['tenant' => \Crypt::decrypt($request->domainId)]);
        }

        return redirect()->route('login')->with('error', 'Your Otp is not correct');
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

    public function logout(Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();

        $request->session()->invalidate(); // Optional: Invalidate session data

        $request->session()->regenerateToken(); // Optional: Regenerate session token for security

        return redirect()->route('login'); // Redirect to login page after logout
    }

}
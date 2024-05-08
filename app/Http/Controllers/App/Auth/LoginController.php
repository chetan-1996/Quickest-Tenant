<?php

namespace App\Http\Controllers\App\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\View\View;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // ... other login methods
    public function showLoginForm(): View
    {
        return view('app.auth.login');
    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate(); // Optional: Invalidate session data

        $request->session()->regenerateToken(); // Optional: Regenerate session token for security

        return redirect()->route('login'); // Redirect to login page after logout
    }
}

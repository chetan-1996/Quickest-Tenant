<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user_data = User::where('id', $this->logged_user->id)->select(['follow_up_note_req_flg','indiamart_integration','lead_merge_flag'])->first();
        return view('settings',compact('user_data'));
    }
}
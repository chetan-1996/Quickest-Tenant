<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $user_param = 0;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            return $next($request);
        });
    }

    public function followUpNotesFlagUpdate(Request $request)
    {
        $input = $request->all();

        $updateArr = ['follow_up_note_req_flg' => $input['follow_up_note_req_flg']];

        User::where("id",$this->company_id)->update($updateArr);
        return $this->sendResponse([], 'Successfully saved!');
    }
}

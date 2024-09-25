<?php

namespace App\Helpers;

use App\Models\UserPermission;

class PermissionCheck
{
    public static function check_permission($permission)
    {
        $user_id = auth()->check() ? auth()->user()->id : 1;
        $company_id = (auth()->user()->company_id) ? auth()->user()->company_id : auth()->user()->id;

//        dd(auth()->user()->permissions);
        if (!empty($user_id)) {
            $user_permissions = UserPermission::query()->join('permissions', 'users_permissions.permission_id', '=', 'permissions.id')->where('users_permissions.user_id', $user_id)->where('users_permissions.company_id', $company_id)->pluck('permissions.slug')->toArray();
            return $user_permissions;
            if (in_array($permission, $user_permissions)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function plan_details_check()
    {

        $user = \Illuminate\Support\Facades\Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $companyExpDet = \Illuminate\Support\Facades\DB::table('users')->where("id", $company_id)->select(["plan_end_date","plan_start_date","created_at","country_id","state_id"])->first();

        return $companyExpDet;

    }
}

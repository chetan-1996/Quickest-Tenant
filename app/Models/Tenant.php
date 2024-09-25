<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'password',
            'mobile_no',
            'password',
            'social_id',
            'social_type',
            'company_name',
            'address',
            'pincode',
            'country_id',
            'state_id',
            'city_id',
            'company_category',
            'website_link',
            'gst_no',
            'profile_icon',
            'company_id',
            'user_id',
            'user_role',
            'role_id',
            'permissions',
            'status',
            'is_owner',
            'otp',
            'facebook_url',
            'twitter_url',
            'instagram_url',
            'linkedin_url',
            'device_key',
            'mobile_device_key',
            'plan_start_date',
            'plan_end_date',
            'remaining_days',
            'city_name',
            'customer_show_flg',
            'is_accepted_terms_condition',
            'plan_id',
            'popupStatus',
            'role_name',
            'plan_status',
            'invite_status',
            'follow_up_note_req_flg',
            'indiamart_integration',
            'assigned_list',
            'facebook_email',
            'facebook_id',
            'facebook_token',
            'storage_capacity',
            'call_url',
            'gmail_url',
            'whatsapp_url',
            'call_code_url',
            'whatsapp_code_url',
            'tradeindia_integration',
            'domain',
            'email_verified_at',
        ];
    }

  /*  public function setPasswordAttribute($value){
        return $this->attributes['password'] = bcrypt($value);

    }*/

}

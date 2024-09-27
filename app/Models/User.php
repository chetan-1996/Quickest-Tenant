<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'mobile_no',
        'email',
        'email_verified_at',
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
        'whatsapp_auth_token',
        'whatsapp_open_chat_token',
        'password',
        'domain',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

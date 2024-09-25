<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'customers';
    protected $fillable = [ 'user_id', 'customer_type', 'name', 'email', 'phone_no','address','pincode','country_id','state_id','city_id','profile_icon','description','status','gst_no', 'company_id','company_name','customer_category_id','customer_lead_id','city_name','assigned_to_user','some_day_flg','country_code','new_lead_flag','whatsapp_no','whatsapp_country_code','currency_name','currency_code','phone_no_country_id','whatsapp_no_country_id','currency_name_country_id','leadgen_id', 'lead_stage_id','lost_reason_id', 'others_reason','generated_id','sp_tmp_flg']; //'state_id',

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNameAttribute($value)
    {
        return ucfirst($value); // Capitalize the first letter of the name
    }
}

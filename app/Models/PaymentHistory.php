<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id', 'user_id', 'cc_avenue_id', 'payment', 'status', 'first_name', 'email', 'mobile_no', 'address', 'city', 'state', 'country', 'pincode','addUsers','add_user',
        'tracking_id','payment_mode','card_name'
    ];
}

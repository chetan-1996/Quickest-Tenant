<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeindiaApiToken extends Model
{
    use HasFactory;
    protected $table = 'tradeindia_api_tokens';
    protected $fillable = [ 'user_id', 'tradeindia_token', 'tradeindia_user_id', 'tradeindia_profile_id'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indiamart_api_tokens extends Model
{
    use HasFactory;
    protected $table = 'indiamart_api_tokens';
    protected $fillable = [ 'user_id', 'indiamart_token'];
}

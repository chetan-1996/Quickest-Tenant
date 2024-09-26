<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plans extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'user_id', 'price', 'yearly_price', 'users_limit', 'status', 'estimate_limit', 'isDefault'];
}

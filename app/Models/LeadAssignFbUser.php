<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadAssignFbUser extends Model
{
    use HasFactory;
    protected $fillable = ['id','company_id', 'user_id','assigned_list'];
}

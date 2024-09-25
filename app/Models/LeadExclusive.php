<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadExclusive extends Model
{
    use HasFactory;
    protected $fillable = [ 'mobile_no','user_id', 'company_id','name'];
}

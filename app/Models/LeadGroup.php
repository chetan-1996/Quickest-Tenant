<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadGroup extends Model
{
    use HasFactory;
    protected $fillable = [ 'name','color_code', 'description', 'status', 'user_id', 'company_id'];
}

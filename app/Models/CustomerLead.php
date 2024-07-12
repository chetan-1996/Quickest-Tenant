<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLead extends Model
{
    use HasFactory;
    protected $table = 'customer_leads';
    protected $fillable = [ 'name', 'description', 'status', 'user_id', 'company_id','is_status'];
}

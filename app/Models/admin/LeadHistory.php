<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadHistory extends Model
{
    use HasFactory;
    protected $connection = 'mysql'; 
    protected $table = 'lead_histroy';
    protected $fillable = [ 'lead_id', 'user_id', 'company_id', 'email', 'domain', 'insert_date' ];
}

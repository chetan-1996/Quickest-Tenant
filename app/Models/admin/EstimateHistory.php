<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateHistory extends Model
{
    use HasFactory;
    protected $connection = 'mysql'; 
    protected $table = 'estimate_histroy';
    protected $fillable = [ 'estimate_id', 'user_id', 'company_id', 'email', 'domain', 'insert_date' ];
}

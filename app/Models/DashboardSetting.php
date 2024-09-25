<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardSetting extends Model
{
    use HasFactory;
    protected $fillable = [ 'permission_id', 'user_id', 'company_id','is_primary'];
}

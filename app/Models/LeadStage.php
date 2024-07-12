<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadStage extends Model
{
    use HasFactory;
    protected $fillable = [ 'name', 'priority', 'is_delete', 'status', 'user_id', 'company_id', 'color_code'];
}

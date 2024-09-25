<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadStageLinkTemplate extends Model
{
    use HasFactory;
    protected $fillable = [ 'template_name', 'lead_id', 'company_id', 'user_id'];
}

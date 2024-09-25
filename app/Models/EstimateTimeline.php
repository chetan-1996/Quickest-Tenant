<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateTimeline extends Model
{
    use HasFactory;

    protected $table = 'customer_timelines';
    protected $fillable = ['estimate_id', 'assigned_to', 'customer_id', 'activity_type', 'activity_name', 'activity_notes', 'internal_remarks', 'follow_up_datetime', 'entry_type', 'is_modified', 'is_follow_up', 'user_id', 'company_id', 'created_by', 'updated_by', 'estimate_version_no', 'net_amount','activity_estimate_status', 'content_id','visit_latitude','visit_longitude','visit_address'];
}

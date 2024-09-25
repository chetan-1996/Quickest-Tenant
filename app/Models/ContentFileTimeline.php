<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentFileTimeline extends Model
{
    use HasFactory;
    protected $table = 'content_file_timelines';
    protected $fillable = ['file_id', 'activity_type', 'activity_name', 'activity_notes', 'internal_remarks', 'user_id', 'company_id', 'created_by', 'updated_by'];
}

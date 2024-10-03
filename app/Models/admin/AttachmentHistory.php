<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttachmentHistory extends Model
{
    use HasFactory;
    protected $connection = 'mysql'; 
    protected $table = 'attachment_histroy';
    protected $fillable = [ 'lead_id', 'attachment_id', 'user_id', 'company_id', 'email', 'domain', 'storage_size', 'insert_date' ];
}

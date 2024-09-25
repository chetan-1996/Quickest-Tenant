<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentFile extends Model
{
    use HasFactory;
    protected $table = 'content_files';
    protected $fillable = [ 'name', 'path', 'status', 'user_id', 'company_id'];
}

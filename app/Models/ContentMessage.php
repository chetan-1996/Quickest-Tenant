<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentMessage extends Model
{
    use HasFactory;
    protected $table = 'content_messages';
    protected $fillable = [ 'name', 'description', 'status', 'user_id', 'company_id'];
}

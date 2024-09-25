<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookPage extends Model
{
    use HasFactory;
    protected $fillable = [ 'page_id', 'page_name', 'form_count', 'user_id', 'company_id'];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'image_one', 'image_two', 'image_three', 'status', 'user_id', 'company_id', 'thumb_image_one', 'thumb_image_two', 'thumb_image_three'];
}

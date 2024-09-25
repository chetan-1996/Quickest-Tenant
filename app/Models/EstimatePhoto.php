<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimatePhoto extends Model
{
    use HasFactory;
    protected $table = 'estimate_photos';
    protected $fillable = [ 'estimate_id', 'product_flag', 'product_id', 'image_one', 'image_two','image_three','thumb_image_one','thumb_image_two','thumb_image_three','user_id','comapny_id'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'folder_id','path', 'file_size', 'user_id', 'company_id'];

    // Define an accessor for the "name" attribute
    public function getPathAttribute($value)
    {
        return ($value)?Storage::disk('s3')->url($value):null;
    }
}

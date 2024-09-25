<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'parent_id', 'user_id', 'company_id', 'description','lead_id'];

    // Relationship to files
    public function files()
    {
        return $this->hasMany(File::class);
    }

    // Relationship to subdirectories
    public function subdirectories()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Deleting event to delete associated files and subdirectories
        static::deleting(function ($folder) {
            $folder->files()->delete();
            $folder->subdirectories()->delete();
        });
    }
}

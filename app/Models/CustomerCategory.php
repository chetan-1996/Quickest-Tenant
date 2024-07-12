<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCategory extends Model
{
    use HasFactory;
    protected $table = 'customer_categories';
    protected $fillable = [ 'name', 'description', 'status', 'user_id', 'company_id'];
}

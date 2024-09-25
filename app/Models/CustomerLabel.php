<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLabel extends Model
{
    use HasFactory;
    protected $table = 'customer_labels';
    protected $fillable = ['customer_id', 'label_id', 'user_id', 'company_id'];
}

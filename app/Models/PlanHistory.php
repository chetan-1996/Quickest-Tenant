<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\admin\Plans;

class PlanHistory extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'plan_id', 'user_limit', 'estimate_limit', 'status', 'start_date', 'end_date'];

    protected $table = 'plan_history';

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function plans(): BelongsTo
    {
        return $this->belongsTo(Plans::class, 'plan_id', 'id');
    }
}

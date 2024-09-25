<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalTemplateCoverPhoto extends Model
{
    use HasFactory;
    protected $fillable = [ 'image_icon', 'proposal_template_id', 'cover_flg', 'user_id', 'company_id'];
}

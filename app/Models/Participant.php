<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = ['user_id', 'discussion_id', 'is_admin'];
}

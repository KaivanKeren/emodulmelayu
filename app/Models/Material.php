<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = ['title', 'description', 'asset', 'user_id', 'model_id'];
}

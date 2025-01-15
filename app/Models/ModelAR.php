<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelAR extends Model
{
    protected $fillable = ['title', 'asset', 'description'];

    protected $table = 'model_a_r_s';
}

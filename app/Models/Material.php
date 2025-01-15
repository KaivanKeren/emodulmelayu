<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = ['title', 'description', 'asset', 'user_id', 'model_id'];

    public function model()
    {
        return $this->belongsTo(ModelAR::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

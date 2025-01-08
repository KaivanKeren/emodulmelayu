<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = ['title', 'category', 'status', 'token'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}

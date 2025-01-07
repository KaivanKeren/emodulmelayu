<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = ['content', 'question_id', 'is_correct'];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}

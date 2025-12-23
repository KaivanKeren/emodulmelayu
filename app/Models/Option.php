<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = ['content', 'question_id', 'is_correct'];
    protected $casts = [
        'is_correct' => 'integer'
        ];
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}

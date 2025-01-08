<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['content', 'question_type', 'assessment_id'];

    public function options()
    {
        return $this->hasMany(Option::class);
    }
}

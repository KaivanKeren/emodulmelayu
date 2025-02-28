<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAssessment extends Model
{
    protected $fillable = [
        'user_id',
        'assessment_id',
        'total_questions',
        'question_answered',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }
}

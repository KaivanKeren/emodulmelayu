<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Assessment extends Model
{
    protected $fillable = ['title', 'category', 'status', 'token', 'token_expires_at', 'timer'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function userAnswers()
    {
        return $this->hasManyThrough(Answer::class, Question::class)
            ->select('user_id')
            ->distinct();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'answers', 'assessment_id', 'user_id')
            ->through('questions')
            ->distinct();
    }

    public function getRespondentsCountAttribute()
    {
        return $this->users()->count();
    }

    protected $casts = [
        'token_expires_at' => 'datetime'
    ];
}

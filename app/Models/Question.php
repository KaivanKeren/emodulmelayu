<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['content', 'question_type', 'image', 'assessment_id'];
    protected $casts = [
        'assessment_id' => "integer"
        ];

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
    
}

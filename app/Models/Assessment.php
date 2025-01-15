<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = ['title', 'category', 'status', 'token', 'token_expires_at'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    protected $casts = [
        'token_expires_at' => 'datetime'
    ];
}

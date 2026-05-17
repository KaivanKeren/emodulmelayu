<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['title', 'content', 'date', 'is_recurring'];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];
}

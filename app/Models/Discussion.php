<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Usamamuneerchaudhary\Commentify\Traits\Commentable;

class Discussion extends Model
{
    // use Commentable;

    protected $fillable = ['title', 'user_id', 'content'];

    public function user()
    {
        return $this->belongsTo(related: User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}

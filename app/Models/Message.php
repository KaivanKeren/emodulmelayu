<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'content',
        'user_id',
        'discussion_id',
        'parent_id'
    ];

    // Load user and replies relationships by default
    protected $with = ['user', 'replies'];

    /**
     * Relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke model Discussion
     */
    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    /**
     * Relasi ke pesan induk
     */
    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    /**
     * Relasi ke balasan pesan
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id')->latest();
    }

    /**
     * Scope untuk pesan induk saja (tanpa parent_id)
     */
    public function scopeRootMessages($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope untuk mendapatkan pesan pada diskusi tertentu
     */
    public function scopeForDiscussion($query, $discussionId)
    {
        return $query->where('discussion_id', $discussionId);
    }

    /**
     * Scope untuk mendapatkan pesan oleh pengguna tertentu
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

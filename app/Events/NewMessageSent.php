<?php

namespace App\Events;

use App\Models\Message;
use Helpers\MessageFormatter;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
        Log::info('Event triggered for message ID: ' . $message->id);  // Debug log
    }

    public function broadcastOn()
    {
        return new Channel('discussion.' . $this->message->discussion_id);
    }

    public function broadcastWith()
    {
        $this->message->load(['user', 'replies.user', 'replies.replies.user']);

        return [
            'code' => 200,
            'message' => 'success',
            'data' => MessageFormatter::format(collect([$this->message]))
        ];
    }
}

<?php

namespace Helpers;

class MessageFormatter 
{
    public static function format($messages) 
    {
        if ($messages->isEmpty()) {
            return null;
        }

        $formattedMessages = [];
        foreach ($messages as $message) {
            $formatted = [
                'id' => $message->id,
                'user' => $message->user->name,
                'message' => $message->content
            ];

            if ($message->replies->isNotEmpty()) {
                $replyData = [];
                foreach ($message->replies as $reply) {
                    $replyData[] = [
                        'id' => $reply->id,
                        'user' => $reply->user->name,
                        'message' => $reply->content,
                        'replies' => self::format($reply->replies)
                    ];
                }
                $formatted['replies'] = $replyData;
            }
            
            $formattedMessages[] = $formatted;
        }

        return count($formattedMessages) === 1 ? $formattedMessages[0] : $formattedMessages;
    }
}
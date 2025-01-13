<?php

namespace App\Http\Controllers;

use App\Events\NewMessageSent;
use App\Models\Discussion;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function store(Request $request, Discussion $discussion)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:messages,id'
        ]);

        $message = $discussion->messages()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id
        ]);

        // Load relations
        $message->load('user');

        // Broadcast
        broadcast(new NewMessageSent($message))->toOthers();

        return response()->json($message);
    }

    public function destroy(Message $message)
    {
        $this->authorize('delete', $message);
        $message->delete();
        return response()->json(['success' => true]);
    }
}

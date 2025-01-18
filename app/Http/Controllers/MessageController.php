<?php

namespace App\Http\Controllers;

use App\Events\NewMessageSent;
use App\Models\Discussion;
use App\Models\Message;
use Helpers\MessageFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Usamamuneerchaudhary\Commentify\Http\Livewire\Comment;

class MessageController extends Controller
{
    public function index()
    {
        try {
            $messages = Message::rootMessages()
                ->with(['user', 'replies.user', 'replies.replies.user'])
                ->get();

            return response()->json([
                'code' => 200,
                'message' => 'success',
                'data' => MessageFormatter::format($messages)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'error',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, $discussion)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string',
                'reply' => 'nullable|exists:messages,id'
            ]);

            $message = Message::create([
                'content' => $validated['message'],
                'user_id' => auth()->id(),
                'discussion_id' => $discussion,
                'parent_id' => $validated['reply'] ?? null
            ]);

            $message->load(['user', 'replies.user', 'replies.replies.user']);

            return response()->json([
                'code' => 200,
                'message' => 'success',
                'data' => MessageFormatter::format(collect([$message]))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'error',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $message = Message::with(['user', 'replies.user', 'replies.replies.user'])
                ->findOrFail($id);

            return response()->json([
                'code' => 200,
                'message' => 'success',
                'data' => MessageFormatter::format(collect([$message]))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'error',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        $this->authorize('delete', $message);
        $message->delete();

        return response()->json(['success' => true]);
    }
}

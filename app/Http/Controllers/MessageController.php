<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Helpers\MessageFormatter;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
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

    public function destroy(Request $request, $discussion, Message $message)
    {
        try {
            // Check if user is authorized to delete the message
            if ($message->user_id !== auth()->id()) {
                return response()->json([
                    'code' => 403,
                    'message' => 'You are not authorized to delete this message'
                ], 403);
            }

            // Delete the message
            $message->delete();

            return response()->json([
                'code' => 200,
                'message' => 'Message deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error deleting message',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

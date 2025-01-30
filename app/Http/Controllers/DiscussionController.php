<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $discussions = Discussion::with('user')
            ->latest()
            ->paginate(10);
        $pendingUsers = User::where('status', 'Pending')->count();

        return view('admin.discussion.index', compact('discussions', 'pendingUsers'));
    }

    public function create()
    {
        $pendingUsers = User::where('status', 'Pending')->count();

        return view('admin.discussion.create', compact('pendingUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $validated['user_id'] = auth()->id();
        Discussion::create($validated);

        return redirect()
            ->route('discussions.index')
            ->with('success', 'Diskusi berhasil dibuat');
    }

    public function show(Discussion $discussion)
    {
        $messages = Message::rootMessages()
            ->with(['user', 'replies.user', 'replies.replies.user'])
            ->where('discussion_id', $discussion->id)
            ->get();
        $pendingUsers = User::where('status', 'Pending')->count();


        return view('admin.discussion.show', compact('discussion', 'messages', 'pendingUsers'));
    }


    public function edit(Discussion $discussion)
    {
        $this->authorize('update', $discussion);
        $pendingUsers = User::where('status', 'Pending')->count();

        return view('admin.discussion.edit', compact('discussion', 'pendingUsers'));
    }

    public function update(Request $request, Discussion $discussion)
    {
        $this->authorize('update', $discussion);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $discussion->update($validated);

        return redirect()
            ->route('discussions.index')
            ->with('success', 'Diskusi berhasil diperbarui');
    }

    public function destroy(Discussion $discussion)
    {
        $this->authorize('delete', $discussion);

        $discussion->delete();

        return redirect()
            ->route('discussions.index')
            ->with('success', 'Diskusi berhasil dihapus');
    }

    public function apiIndex()
    {
        $discussions = Discussion::with('user')->get();

        return response()->json([
            'code' => 200,
            'message' => 'Discussion retrieved successfully',
            'data' => $discussions
        ], 200);
    }

    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Menambahkan user_id dari user yang sedang login
        $validated['user_id'] = auth()->id();

        $discussion = Discussion::create($validated);

        return response()->json([
            'code' => 200,
            'message' => 'Discussion created successfully',
            'data' => $discussion
        ], 201);
    }

    public function apiShow($id)
    {
        $discussion = Discussion::with('user')->findOrFail($id);

        return response()->json([
            'code' => 200,
            'message' => 'Discussion retrieved successfully',
            'data' => $discussion
        ], 200);
    }

    public function apiUpdate(Request $request, $id)
    {
        $discussion = Discussion::findOrFail($id);

        // Cek apakah user yang login adalah pemilik diskusi
        if ($discussion->user_id !== auth()->id()) {
            return response()->json([
                'code' => 403,
                'message' => 'Unauthorized action'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
        ]);

        $discussion->update($validated);

        return response()->json([
            'code' => 200,
            'message' => 'Discussion updated successfully',
            'data' => $discussion
        ], 200);
    }

    public function apiDestroy($id)
    {
        $discussion = Discussion::findOrFail($id);

        // Cek apakah user yang login adalah pemilik diskusi
        if ($discussion->user_id !== auth()->id()) {
            return response()->json([
                'code' => 403,
                'message' => 'Unauthorized action'
            ], 403);
        }

        $discussion->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Discussion deleted successfully'
        ], 200);
    }
}

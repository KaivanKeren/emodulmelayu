<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscussionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Discussion::with('user')
            ->withCount(['messages as participant_count' => function ($query) {
                $query->select(DB::raw('count(distinct user_id)'));
            }]);

        // Handle sorting
        if ($request->has('sort')) {
            $direction = $request->input('direction', 'asc');

            if ($request->input('sort') === 'title') {
                $query->orderBy('title', $direction);
            }
        } else {
            // Default sorting (probably by created_at desc)
            $query->orderBy('title', 'asc');
        }

        $discussions = $query->paginate(10);
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
        $discussions = Discussion::with('user')
            ->withCount(['messages as participant_count' => function ($query) {
                $query->select(DB::raw('count(distinct user_id)'));
            }])
            ->orderBy('title', 'asc')
            ->get();

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

        if ($discussion->user_id != auth()->id()) {
            return response()->json([
                'code' => 403,
                'message' => 'Unauthorized action'
            ], 403);
        }
        
        // return response()->json(['id' => auth()->id()]);

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

        if ($discussion->user_id != auth()->id()) {
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

<?php

namespace App\Http\Controllers;

use App\Models\FilterMessage;
use App\Models\User;
use Illuminate\Http\Request;

class FilterMessageController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'created_at'); // Default sort by created_at
        $direction = $request->input('direction', 'desc'); // Default direction desc

        // Validate sort parameter to prevent SQL injection
        $allowedSorts = ['word', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        // Validate direction parameter
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';
        $filterWords = FilterMessage::orderBy($sort, $direction)
            ->paginate(10);

        $pendingUsers = User::where('status', 'Pending')->count();


        return view('admin.filter_message.index', compact('filterWords', 'pendingUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'word' => 'required|string|max:255',
        ]);

        FilterMessage::create($validated);

        return redirect()
            ->route('filter_messages.index')
            ->with('success', 'Filter message berhasil dibuat');
    }

    public function destroy(FilterMessage $filterMessage)
    {
        $filterMessage->delete();

        return redirect()
            ->route('filter_messages.index')
            ->with('success', 'Filter message berhasil dihapus');
    }

    // API Method
    public function apiIndex()
    {
        $filterWords = FilterMessage::latest()
            ->paginate(10);

        return response()->json([
            'code' => 200,
            'message' => 'Data filter message',
            'data' => $filterWords
        ]);
    }

    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'word' => 'required|string|max:255',
        ]);

        $data = FilterMessage::create($validated);

        return response()->json([
            'code' => 201,
            'message' => 'Filter message berhasil dibuat',
            'data' => $data
        ]);
    }

    public function apiDestroy(FilterMessage $filterMessage)
    {
        $filterMessage->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Filter message berhasil dihapus'
        ]);
    }
}

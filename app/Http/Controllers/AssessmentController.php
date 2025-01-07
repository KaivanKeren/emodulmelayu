<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssessmentController extends Controller
{
    // Web CRUD Methods
    public function index()
    {
        $assessments = Assessment::latest()->paginate(10);
        return view('assessments.index', compact('assessments'));
    }

    public function create()
    {
        return view('assessments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|max:255',
            'status' => 'required|in:belum terbuka,terbuka,terjawab,selesai'
        ]);

        $validated['token'] = Str::random(6);
        Assessment::create($validated);

        return redirect()->route('assessments.index')
            ->with('success', 'Assessment created successfully.');
    }

    public function edit(Assessment $assessment)
    {
        return view('assessments.edit', compact('assessment'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|max:255',
            'status' => 'required|in:belum terbuka,terbuka,terjawab,selesai'
        ]);

        $assessment->update($validated);

        return redirect()->route('assessments.index')
            ->with('success', 'Assessment updated successfully');
    }

    public function destroy(Assessment $assessment)
    {
        $assessment->delete();
        return redirect()->route('assessments.index')
            ->with('success', 'Assessment deleted successfully');
    }

    // API Methods
    public function apiIndex()
    {
        return response()->json([
            'code' => 200,
            'message' => 'Assessment retrieved successfully',
            'data' => Assessment::latest()->get()
        ]);
    }

    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|max:255',
            'status' => 'required|in:belum terbuka,terbuka,terjawab,selesai'
        ]);

        $validated['token'] = Str::random(6);
        $assessment = Assessment::create($validated);

        return response()->json([
            'code' => 201,
            'message' => 'Assessment created successfully.',
            'data' => $assessment
        ], 201);
    }

    public function apiShow(Assessment $assessment)
    {
        return response()->json([
            'code' => 200,
            'message' => 'Assessment detail retrieved successfully',
            'data' => $assessment
        ]);
    }

    public function apiUpdate(Request $request, Assessment $assessment)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|max:255',
            'status' => 'required|in:belum terbuka,terbuka,terjawab,selesai'
        ]);

        $assessment->update($validated);

        return response()->json([
            'code' => 200,
            'message' => 'Assessment updated successfully',
            'data' => $assessment
        ]);
    }

    public function apiDestroy(Assessment $assessment)
    {
        $assessment->delete();
        return response()->json([
            'code' => 200,
            'message' => 'Assessment deleted successfully'
        ]);
    }
}

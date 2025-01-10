<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnswerResource;
use App\Http\Resources\AssessmentResource;
use App\Models\Answer;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssessmentController extends Controller
{
    // Web CRUD Methods
    public function index()
    {
        $assessments = Assessment::latest()->paginate(10);
        return view('admin.assessments.index', compact('assessments'));
    }

    public function create()
    {
        return view('admin.assessments.create');
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
        return view('admin.assessments.edit', compact('assessment'));
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

    public function show(Assessment $assessment)
    {
        $assessment->load(['questions.options']);

        return view('admin.assessments.show', [
            'assessment' => $assessment
        ]);
    }
    public function apiShow(Assessment $assessment)
    {
        $assessment->load(['questions.options']);

        return response()->json([
            'code' => 200,
            'message' => 'Assessment retrieved successfully',
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

    public function apiGetAllResults(Assessment $assessment)
    {
        // Ambil semua pertanyaan terkait dengan assessment
        $questions = $assessment->questions()->with('options')->get();

        // Data untuk hasil semua pertanyaan
        $results = $questions->map(function ($question) {
            // Ambil jawaban pengguna untuk setiap pertanyaan
            $answers = Answer::where('question_id', $question->id)
                ->where('user_id', Auth::id())
                ->with(['option', 'question'])
                ->get();

            // Ambil opsi yang benar untuk setiap pertanyaan
            $correctOptions = $question->options->where('is_correct', true);

            // Format data hasil
            return [
                'question_id' => $question->id,
                'question_type' => $question->question_type,
                'answers' => AnswerResource::collection($answers),
                'score' => $answers->first() ? $answers->first()->score : 0,
                'correct_options' => $correctOptions,
            ];
        });

        // Hitung total score
        $totalScore = $results->sum('score');

        // Kembalikan respons JSON
        return response()->json([
            'assessment_id' => $assessment->id,
            'results' => $results,
            'total_score' => $totalScore
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionResource;
use App\Models\Assessment;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::with('options')->get();
        return view('questions.index', compact('questions'));
    }

    public function create(Request $request)
    {
        // Get assessment from the query parameter
        $assessment = Assessment::findOrFail($request->query('assessment'));
        return view('admin.questions.create', compact('assessment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_text' => 'required|string|max:255',
            'question_type' => 'required|in:single_choice,multiple_choice',
            'assessment_id' => 'required|exists:assessments,id',
            'options' => 'required|array|min:2',
            'correct_answer' => $request->input('question_type') === 'single_choice'
                ? 'required|numeric'
                : 'required|array|min:1'
        ]);

        // Create question
        $question = Question::create([
            'content' => $validated['question_text'],
            'type' => $validated['question_type'],
            'assessment_id' => $validated['assessment_id']
        ]);

        // Handle options and correct answers
        foreach ($validated['options'] as $index => $optionContent) {
            $isCorrect = false;

            if ($validated['question_type'] === 'single_choice') {
                $isCorrect = $index == $validated['correct_answer'];
            } else {
                $isCorrect = isset($validated['correct_answer'][$index]);
            }

            $question->options()->create([
                'content' => $optionContent,
                'is_correct' => $isCorrect
            ]);
        }

        return redirect()
            ->route('assessments.show', $validated['assessment_id'])
            ->with('success', 'Pertanyaan berhasil ditambahkan');
    }

    public function show(Question $question)
    {
        return view('questions.show', compact('question'));
    }

    public function edit(Question $question)
    {
        return view('questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:255',
            'assessment_id' => 'required|exists:assessments,id',
            'options' => 'required|array|min:2',
            'options.*.content' => 'required|string|max:255',
            'options.*.is_correct' => 'required|boolean'
        ]);

        $question->update([
            'content' => $validated['content'],
            'assessment_id' => $validated['assessment_id']
        ]);

        // Delete existing options and create new ones
        $question->options()->delete();

        foreach ($validated['options'] as $optionData) {
            $question->options()->create([
                'content' => $optionData['content'],
                'is_correct' => $optionData['is_correct']
            ]);
        }

        return redirect()->route('questions.index')
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(Question $question)
    {
        $question->delete();
        // Options will be automatically deleted due to onDelete('cascade')

        return redirect()->route('questions.index')
            ->with('success', 'Question deleted successfully.');
    }

    // API Method
    public function apiIndex(Assessment $assessment)
    {
        $questions = $assessment->questions()->with(['options'])->paginate(10);

        return response()->json([
            'code' => 200,
            'message' => 'Questions retrieved successfully',
            'data' => QuestionResource::collection($questions)
        ]);
    }

    public function apiStore(Request $request, Assessment $assessment)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:255',
            'question_type' => 'required|string|in:single_choice,multiple_choice',
            'options' => 'required|array|min:2',
            'options.*.content' => 'required|string|max:255',
            'options.*.is_correct' => 'required|boolean'
        ]);

        // Validate that only one option is correct for single-choice questions
        if ($validated['question_type'] === 'single_choice') {
            $correctOptions = array_filter($validated['options'], fn($option) => $option['is_correct']);
            if (count($correctOptions) !== 1) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Single choice questions must have exactly one correct answer',
                    'data' => null
                ], 422);
            }
        }

        // Validate that at least one option is correct for multiple-choice questions
        if ($validated['question_type'] === 'multiple_choice') {
            $correctOptions = array_filter($validated['options'], fn($option) => $option['is_correct']);
            if (empty($correctOptions)) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Multiple choice questions must have at least one correct answer',
                    'data' => null
                ], 422);
            }
        }

        $question = $assessment->questions()->create([
            'content' => $validated['content'],
            'question_type' => $validated['question_type']
        ]);

        foreach ($validated['options'] as $optionData) {
            $question->options()->create([
                'content' => $optionData['content'],
                'is_correct' => $optionData['is_correct']
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Question created successfully',
            'data' => new QuestionResource($question->load('options'))
        ]);
    }

    public function apiShow(Assessment $assessment, $question_id)
    {
        $question = Question::where('assessment_id', $assessment->id)
            ->where('id', $question_id)
            ->firstOrFail();

        return response()->json([
            'code' => 200,
            'message' => 'Question retrieved successfully',
            'data' => new QuestionResource($question->load('options'))
        ]);
    }

    public function apiUpdate(Request $request, Assessment $assessment, $question_id)
    {
        $question = Question::where('assessment_id', $assessment->id)
            ->where('id', $question_id)
            ->firstOrFail();

        $validated = $request->validate([
            'content' => 'required|string|max:255',
            'question_type' => 'required|string|in:single_choice,multiple_choice',
            'options' => 'required|array|min:2',
            'options.*.content' => 'required|string|max:255',
            'options.*.is_correct' => 'required|boolean'
        ]);

        // Validate that only one option is correct for single-choice questions
        if ($validated['question_type'] === 'single_choice') {
            $correctOptions = array_filter($validated['options'], fn($option) => $option['is_correct']);
            if (count($correctOptions) !== 1) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Single choice questions must have exactly one correct answer',
                    'data' => null
                ], 422);
            }
        }

        // Validate that at least one option is correct for multiple-choice questions
        if ($validated['question_type'] === 'multiple_choice') {
            $correctOptions = array_filter($validated['options'], fn($option) => $option['is_correct']);
            if (empty($correctOptions)) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Multiple choice questions must have at least one correct answer',
                    'data' => null
                ], 422);
            }
        }

        $question->update([
            'content' => $validated['content'],
            'question_type' => $validated['question_type']
        ]);

        // Delete existing options and create new ones
        $question->options()->delete();

        foreach ($validated['options'] as $optionData) {
            $question->options()->create([
                'content' => $optionData['content'],
                'is_correct' => $optionData['is_correct']
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Question updated successfully',
            'data' => new QuestionResource($question->load('options'))
        ]);
    }

    public function apiDestroy(Assessment $assessment, $question_id)
    {
        $question = Question::where('assessment_id', $assessment->id)
            ->where('id', $question_id)
            ->firstOrFail();

        $question->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Question deleted successfully',
            'data' => null
        ]);
    }
}

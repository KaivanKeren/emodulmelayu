<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionResource;
use App\Models\Assessment;
use App\Models\Option;
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
        // Validasi data yang diterima dari form
        $validated = $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:255',
            'questions.*.question_type' => 'required|in:single_choice,multiple_choice',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.correct_answer' => function ($attribute, $value, $fail) use ($request) {
                $questionType = $request->input('questions')[count($request->input('questions')) - 1]['question_type'];
                if ($questionType == 'single_choice' && !is_numeric($value)) {
                    $fail('The correct answer must be a number for single choice questions.');
                } elseif ($questionType == 'multiple_choice' && !is_array($value)) {
                    $fail('The correct answers must be an array for multiple choice questions.');
                }
            },
        ]);

        // Loop untuk setiap pertanyaan yang dikirimkan
        foreach ($validated['questions'] as $questionData) {
            // Membuat pertanyaan
            $question = Question::create([
                'content' => $questionData['question_text'],
                'type' => $questionData['question_type'],
                'assessment_id' => $request->assessment_id, // Pastikan assessment_id dikirim dari form
            ]);

            // Mengatur pilihan jawaban dan jawaban yang benar
            foreach ($questionData['options'] as $index => $optionContent) {
                $isCorrect = false;

                // Cek apakah ini pertanyaan pilihan tunggal atau ganda
                if ($questionData['question_type'] === 'single_choice') {
                    $isCorrect = $index == $questionData['correct_answer'];
                } else {
                    // Untuk pilihan ganda, cek apakah pilihan ini benar (bisa lebih dari satu jawaban benar)
                    $isCorrect = in_array($index, (array) $questionData['correct_answer']);
                }

                // Simpan pilihan jawaban untuk pertanyaan ini
                Option::create([
                    'question_id' => $question->id,
                    'content' => $optionContent,
                    'is_correct' => $isCorrect,
                ]);
            }
        }

        return redirect()->route('assessments.show', $request->assessment_id)
            ->with('success', 'Pertanyaan berhasil ditambahkan.');
    }

    public function show(Question $question)
    {
        return view('questions.show', compact('question'));
    }

    public function edit(Question $question)
    {
        $question->load('assessment');
        return view('admin.questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'question_text' => 'required|string|max:255',
            'assessment_id' => 'required|exists:assessments,id',
            'question_type' => 'required|in:single_choice,multiple_choice',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
        ]);

        // Update question
        $question->update([
            'content' => $validated['question_text'],
            'assessment_id' => $validated['assessment_id'],
            'question_type' => $validated['question_type']
        ]);

        // Get correct answers
        $correctAnswers = [];
        if ($validated['question_type'] === 'single_choice') {
            $correctAnswers = [(int) $request->input('correct_answer')];
        } else {
            // For multiple choice, get all checked options
            $correctAnswers = array_keys(array_filter($request->input('correct_answer', [])));
        }

        // Delete existing options
        $question->options()->delete();

        // Create new options with correct answers
        foreach ($validated['options'] as $index => $optionContent) {
            $question->options()->create([
                'content' => $optionContent,
                'is_correct' => in_array($index, $correctAnswers)
            ]);
        }

        return redirect()->route('assessments.show', $question->assessment_id)
            ->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    public function destroy(Question $question)
    {
        // Store assessment_id before deleting the question
        $assessmentId = $question->assessment_id;

        // Delete the question (options will be deleted via cascade)
        $question->delete();

        return redirect()->route('assessments.show', $assessmentId)
            ->with('success', 'Pertanyaan berhasil dihapus.');
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

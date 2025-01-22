<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionResource;
use App\Models\Assessment;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        // Validasi dasar untuk struktur data
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:255',
            'questions.*.question_type' => 'required|in:single_choice,multiple_choice',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Validasi untuk setiap pertanyaan
        foreach ($request->questions as $index => $question) {
            $validationRules = [];

            if ($question['question_type'] === 'single_choice') {
                $validationRules["questions.{$index}.correct_answer"] = [
                    'required',
                    'numeric',
                    'min:0',
                    'max:' . (count($question['options']) - 1)
                ];
            } else {
                $validationRules["questions.{$index}.correct_answer"] = [
                    'required',
                    'array',
                    'min:1'
                ];
                $validationRules["questions.{$index}.correct_answer.*"] = [
                    'numeric',
                    'distinct',
                    'min:0',
                    'max:' . (count($question['options']) - 1)
                ];
            }

            $request->validate($validationRules, [
                "questions.{$index}.correct_answer.required" => 'Pertanyaan #' . ($index + 1) . ' harus memiliki jawaban yang benar.',
                "questions.{$index}.correct_answer.*.numeric" => 'Jawaban yang benar harus berupa angka.',
                "questions.{$index}.correct_answer.*.distinct" => 'Jawaban yang benar tidak boleh duplikat.',
                "questions.{$index}.correct_answer.*.min" => 'Indeks jawaban tidak valid.',
                "questions.{$index}.correct_answer.*.max" => 'Indeks jawaban tidak valid.',
                "questions.{$index}.image.image" => 'File harus berupa gambar',
                "questions.{$index}.image.mimes" => 'Format gambar harus jpeg, png, jpg, atau gif',
                "questions.{$index}.image.max" => 'Ukuran gambar tidak boleh lebih dari 2MB'
            ]);
        }

        try {
            DB::beginTransaction();

            foreach ($request->questions as $questionData) {
                // Handle image upload
                $imagePath = null;
                if (isset($questionData['image']) && $questionData['image']) {
                    $imagePath = $questionData['image']->store('question-images', 'public');
                }

                // Membuat pertanyaan
                $question = Question::create([
                    'content' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'assessment_id' => $request->assessment_id,
                    'image' => $imagePath, // Tambahkan image_path ke database
                ]);

                // Menyiapkan array untuk menyimpan pilihan jawaban
                $options = [];
                foreach ($questionData['options'] as $index => $optionContent) {
                    $isCorrect = false;

                    if ($questionData['question_type'] === 'single_choice') {
                        $isCorrect = $index == $questionData['correct_answer'];
                    } else {
                        $isCorrect = in_array(strval($index), (array)$questionData['correct_answer']);
                    }

                    $options[] = [
                        'question_id' => $question->id,
                        'content' => $optionContent,
                        'is_correct' => $isCorrect,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Bulk insert untuk options
                Option::insert($options);
            }

            DB::commit();

            return redirect()
                ->route('assessments.show', $request->assessment_id)
                ->with('success', 'Pertanyaan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Jika terjadi error, hapus file yang sudah terupload (jika ada)
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pertanyaan. Silakan coba lagi.');
        }
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

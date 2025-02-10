<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionResource;
use App\Models\Assessment;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function create(Request $request)
    {
        $pendingUsers = User::where('status', 'Pending')->count();
        // Get assessment from the query parameter
        $assessment = Assessment::findOrFail($request->query('assessment'));
        return view('admin.questions.create', compact('assessment', 'pendingUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'questions' => 'required|array|min:1',
            'questions.*.content' => 'required|string',
            'questions.*.question_type' => 'required|in:single_choice,multiple_choice',
            'questions.*.options' => 'required|array|min:1',
        ]);

        foreach ($request->questions as $index => $question) {
            $cleanContent = $this->sanitizeHtml($question['content']);

            foreach ($question['options'] as $optionIndex => $optionContent) {
                $cleanOptionContent = $this->sanitizeHtml($optionContent);
                if (empty($cleanOptionContent)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Pilihan jawaban #" . ($optionIndex + 1) . " pada pertanyaan #" . ($index + 1) . " harus diisi dengan konten yang valid.",
                        'errors' => [
                            "questions.{$index}.options.{$optionIndex}" => ["Pilihan jawaban tidak boleh kosong"]
                        ]
                    ], 422);
                }
            }

            $validationRules = [];
            if ($question['question_type'] === 'single_choice') {
                $validationRules["questions.{$index}.correct_answer"] = 'required|numeric|min:0|max:' . (count($question['options']) - 1);
            } else {
                $validationRules["questions.{$index}.correct_answer"] = 'required|array|min:1';
                $validationRules["questions.{$index}.correct_answer.*"] = 'numeric|distinct|min:0|max:' . (count($question['options']) - 1);
            }

            $request->validate($validationRules);
        }

        try {
            DB::beginTransaction();

            foreach ($request->questions as $questionData) {
                $cleanContent = $this->sanitizeHtml($questionData['content']);

                $question = Question::create([
                    'content' => $cleanContent,
                    'question_type' => $questionData['question_type'],
                    'assessment_id' => $request->assessment_id,
                ]);

                $options = [];
                foreach ($questionData['options'] as $index => $optionContent) {
                    $cleanOptionContent = $this->sanitizeHtml($optionContent);
                    $isCorrect = $questionData['question_type'] === 'single_choice' ?
                        $index == $questionData['correct_answer'] :
                        in_array(strval($index), (array) $questionData['correct_answer']);

                    $options[] = [
                        'question_id' => $question->id,
                        'content' => $cleanOptionContent,
                        'is_correct' => $isCorrect,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Option::insert($options);
            }

            DB::commit();

            return redirect()
                ->route('assessments.show', $request->assessment_id)
                ->with('success', 'Pertanyaan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan pertanyaan.',
            ], 500);
        }
    }

    private function sanitizeHtml($content)
    {
        if (empty($content)) {
            return '';
        }

        $allowedTags = [
            'p',
            'br',
            'strong',
            'em',
            'u',
            's',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'ol',
            'ul',
            'li',
            'sub',
            'sup',
            'a',
            'span',
            'div',
            'img'
        ];

        // Mengizinkan img dengan atribut src base64
        $cleanContent = preg_replace_callback(
            '/<img[^>]+src=[\'\"](data:image\/[^;]+;base64,[^\'\"]+)[\'\"][^>]*>/i',
            function ($matches) {
                return '<p><img src="' . $matches[1] . '"></p>';
            },
            $content
        );

        // Bersihkan HTML dari tag yang tidak diizinkan
        $cleanContent = strip_tags($cleanContent, '<' . implode('><', $allowedTags) . '>');

        return trim($cleanContent);
    }


    public function edit(Question $question)
    {
        $question->load('assessment');
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.questions.edit', compact('question', 'pendingUsers'));
    }

    public function update(Request $request, Question $question)
    {
        // Sanitize the HTML content first
        $questionText = $this->sanitizeHtml($request->input('question_text'));
        $options = array_map(function ($option) {
            return $this->sanitizeHtml($option);
        }, $request->input('options', []));

        // Check if content is empty after sanitization
        if (empty(trim(strip_tags($questionText)))) {
            return back()
                ->withInput()
                ->withErrors(['question_text' => 'Pertanyaan tidak boleh kosong.']);
        }

        // Check if any option is empty after sanitization
        foreach ($options as $index => $option) {
            if (empty(trim(strip_tags($option)))) {
                return back()
                    ->withInput()
                    ->withErrors(['options.' . $index => 'Jawaban tidak boleh kosong.']);
            }
        }

        // Validate the request
        $validated = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'question_type' => 'required|in:single_choice,multiple_choice',
            'options' => 'required|array|min:2',
            'correct_answer' => function ($attribute, $value, $fail) use ($request) {
                if ($request->input('question_type') === 'single_choice') {
                    if (!is_numeric($value)) {
                        $fail('Pilih salah satu jawaban yang benar.');
                    }
                } else {
                    if (!is_array($value) || empty($value)) {
                        $fail('Pilih setidaknya satu jawaban yang benar.');
                    }
                }
            },
        ]);

        // Begin transaction
        DB::beginTransaction();

        try {
            // Update question details
            $question->update([
                'content' => $questionText,
                'assessment_id' => $validated['assessment_id'],
                'question_type' => $validated['question_type']
            ]);

            // Get correct answers
            $correctAnswers = [];
            if ($validated['question_type'] === 'single_choice') {
                $correctAnswers = [(int) $request->input('correct_answer')];
            } else {
                $correctAnswers = array_keys($request->input('correct_answer', []));
            }

            // Validate that at least one correct answer is selected
            if (empty($correctAnswers)) {
                throw new ValidationException(validator([], [], [
                    'correct_answer' => 'Pilih minimal satu jawaban yang benar.'
                ]));
            }

            // Delete existing options
            $question->options()->delete();

            // Create new options with correct answers
            foreach ($options as $index => $optionContent) {
                $isCorrect = $validated['question_type'] === 'single_choice'
                    ? $index === $correctAnswers[0]
                    : in_array($index, $correctAnswers);

                $question->options()->create([
                    'content' => $optionContent,
                    'is_correct' => $isCorrect
                ]);
            }

            DB::commit();

            return redirect()
                ->route('assessments.show', $question->assessment_id)
                ->with('success', 'Pertanyaan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui pertanyaan. Silakan coba lagi.']);
        }
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

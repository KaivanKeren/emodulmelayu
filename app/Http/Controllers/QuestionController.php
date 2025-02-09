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
        Log::info('Test Store Question');

        // Validasi dasar untuk struktur data
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'questions' => 'required|array|min:1',
            'questions.*.content' => 'required|string',
            'questions.*.question_type' => 'required|in:single_choice,multiple_choice',
            'questions.*.options' => 'required|array|min:1',
        ]);

        // Pre-process dan validasi konten
        foreach ($request->questions as $index => $question) {
            // Validasi konten pertanyaan
            $cleanContent = $this->sanitizeHtml($question['content']);
            if (empty($cleanContent)) {
                return response()->json([
                    'success' => false,
                    'message' => "Pertanyaan #" . ($index + 1) . " harus diisi dengan konten yang valid.",
                    'errors' => [
                        "questions.{$index}.content" => ["Pertanyaan tidak boleh kosong"]
                    ]
                ], 422);
            }

            // Validasi konten pilihan jawaban
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

            // Validasi jawaban yang benar
            $validationRules = [];
            if ($question['question_type'] === 'single_choice') {
                $validationRules["questions.{$index}.correct_answer"] = 'required|numeric|min:0|max:' . (count($question['options']) - 1);
            } else {
                $validationRules["questions.{$index}.correct_answer"] = 'required|array|min:1';
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
            ]);
        }

        Log::info('Memulai proses store pertanyaan', ['request_data' => $request->all()]);

        try {
            DB::beginTransaction();

            foreach ($request->questions as $questionData) {
                // Clean and sanitize the HTML content
                $cleanContent = $this->sanitizeHtml($questionData['content']);

                // Membuat pertanyaan
                $question = Question::create([
                    'content' => $cleanContent,
                    'question_type' => $questionData['question_type'],
                    'assessment_id' => $request->assessment_id,
                ]);

                // Menyiapkan array untuk menyimpan pilihan jawaban
                $options = [];
                foreach ($questionData['options'] as $index => $optionContent) {
                    $cleanOptionContent = $this->sanitizeHtml($optionContent);
                    $isCorrect = false;

                    if ($questionData['question_type'] === 'single_choice') {
                        $isCorrect = $index == $questionData['correct_answer'];
                    } else {
                        $isCorrect = in_array(strval($index), (array)$questionData['correct_answer']);
                    }

                    $options[] = [
                        'question_id' => $question->id,
                        'content' => $cleanOptionContent,
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

            Log::error('Gagal menyimpan pertanyaan.', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token']),
                'failed_query' => optional(DB::getQueryLog())
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan pertanyaan. Silakan coba lagi.'
            ], 500);
        }
    }


    private function sanitizeHtml($content)
    {
        if (empty($content)) {
            return '';
        }

        // Simpan formula dalam array sementara
        $formulas = [];
        $placeholder = 'FORMULA_PLACEHOLDER_';

        // Extract formula tags dan simpan
        $content = preg_replace_callback(
            '/<span class="ql-formula"[^>]*data-value="([^"]*)"[^>]*>.*?<\/span>/i',
            function ($matches) use (&$formulas, $placeholder) {
                $index = count($formulas);
                $formulas[] = $matches[0]; // Simpan seluruh tag formula
                return $placeholder . $index;
            },
            $content
        );

        // Daftar tag HTML yang diizinkan
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
            'img',
            'a',
            'span',
            'div'
        ];

        // Bersihkan konten HTML
        $cleanContent = strip_tags($content, '<' . implode('><', $allowedTags) . '>');

        // Hapus spasi berlebih dan karakter whitespace
        $cleanContent = trim($cleanContent);

        // Hapus tag HTML kosong (contoh: <p></p>)
        $cleanContent = preg_replace('/<[^\/>]*>(\s*)<\/[^>]*>/', '', $cleanContent);

        // Hapus tag HTML yang hanya berisi spasi
        $cleanContent = preg_replace('/<[^>]*>(\s+)<\/[^>]*>/', '', $cleanContent);

        // Ubah multiple newlines menjadi satu newline
        $cleanContent = preg_replace('/(\R){2,}/', "\n", $cleanContent);

        // Kembalikan formula ke konten
        foreach ($formulas as $index => $formula) {
            $cleanContent = str_replace($placeholder . $index, $formula, $cleanContent);
        }

        // Pastikan konten tidak hanya berisi tag HTML kosong
        $textContent = trim(strip_tags(str_replace($formulas, [''], $cleanContent)));
        if (empty($textContent) && empty($formulas)) {
            return '';
        }

        return $cleanContent;
    }

    public function edit(Question $question)
    {
        $question->load('assessment');
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.questions.edit', compact('question', 'pendingUsers'));
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'question_text' => 'required|string|max:255',
            'assessment_id' => 'required|exists:assessments,id',
            'question_type' => 'required|in:single_choice,multiple_choice',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
            'correct_answer' => $request->input('question_type') === 'single_choice'
                ? 'required|integer'
                : 'required|array',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($question->image) {
                Storage::delete($question->image);
            }

            // Store new image
            $imagePath = $request->file('image')->store('question-images', 'public');
            $question->image = $imagePath;
        } elseif ($request->input('remove_image') === '1') {
            // Option to remove existing image
            if ($question->image) {
                Storage::delete($question->image);
                $question->image = null;
            }
        }

        // Update question details
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
            $correctAnswers = $request->input('correct_answer', []);
        }

        // Delete existing options
        $question->options()->delete();

        // Create new options with correct answers
        foreach ($validated['options'] as $index => $optionContent) {
            $isCorrect = false;

            if ($validated['question_type'] === 'single_choice') {
                $isCorrect = $index === $correctAnswers[0];
            } else {
                // For multiple choice, check if the index is in the correct answers array
                $isCorrect = in_array($index, array_keys($correctAnswers));
            }

            $question->options()->create([
                'content' => $optionContent,
                'is_correct' => $isCorrect
            ]);
        }

        // Save the question with potential image update
        $question->save();

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

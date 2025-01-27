<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnswerResource;
use App\Http\Resources\AssessmentResource;
use App\Models\Answer;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AssessmentController extends Controller
{
    // Web CRUD Methods
    public function index()
    {
        $assessments = Assessment::latest()->paginate(10);
        return view('admin.assessments.index', compact('assessments'));
    }

    public function filter(Request $request)
    {
        // Retrieve filters from request
        $filters = $request->only(['title', 'category', 'status']);

        // Build query with optional filters
        $query = Assessment::query();

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Get users with pagination
        $assessments = $query->paginate(10);

        // Return view with assessments and filters
        return view('admin.assessments.index', compact('assessments', 'filters'));
    }

    public function create()
    {
        return view('admin.assessments.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|max:255',
                'category' => 'required|max:255',
                'status' => 'required|in:Belum Terbuka,Terbuka,Terjawab,Selesai',
                'questions' => 'required|array|min:1',
                'questions.*.content' => 'required|string|max:255',
                'questions.*.question_type' => 'required|in:single_choice,multiple_choice',
                'questions.*.options' => 'required|array|min:1',
                'questions.*.options.*' => 'required|string|max:255',
                'questions.*.correct_answer' => 'required_if:questions.*.question_type,single_choice',
                'questions.*.correct_answer.*' => 'required_if:questions.*.question_type,multiple_choice',
                'questions.*.image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Added image validation
            ], [
                'title.required' => 'Judul assessment wajib diisi',
                'title.max' => 'Judul tidak boleh lebih dari 255 karakter',
                'category.required' => 'Kategori assessment wajib diisi',
                'status.required' => 'Status assessment wajib diisi',
                'status.in' => 'Status yang dipilih tidak valid',
                'questions.required' => 'Assessment harus memiliki minimal satu pertanyaan',
                'questions.*.content.required' => 'Teks pertanyaan wajib diisi',
                'questions.*.question_type.required' => 'Tipe pertanyaan wajib dipilih',
                'questions.*.question_type.in' => 'Tipe pertanyaan tidak valid',
                'questions.*.options.required' => 'Setiap pertanyaan harus memiliki pilihan jawaban',
                'questions.*.options.min' => 'Setiap pertanyaan harus memiliki minimal satu pilihan jawaban',
                'questions.*.options.*.required' => 'Teks pilihan jawaban wajib diisi',
                'questions.*.correct_answer.required_if' => 'Pilihan jawaban benar wajib dipilih untuk pertanyaan pilihan ganda',
                'questions.*.correct_answer.*.required_if' => 'Pilihan jawaban benar wajib dipilih untuk pertanyaan pilihan ganda kompleks',
                'questions.*.image.image' => 'File harus berupa gambar',
                'questions.*.image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
                'questions.*.image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB'
            ]);

            DB::beginTransaction();

            $tokenData = [
                'token' => null,
                'token_expires_at' => null
            ];

            // Generate token only if status is 'Terbuka'
            if ($validated['status'] === 'Terbuka') {
                $tokenData = [
                    'token' => substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6),
                    'token_expires_at' => now()->addMinutes(30)
                ];
            }

            $assessment = Assessment::create([
                'title' => $validated['title'],
                'category' => $validated['category'],
                'status' => $validated['status'],
                'token' => $tokenData['token'],
                'token_expires_at' => $tokenData['token_expires_at']
            ]);

            foreach ($validated['questions'] as $questionData) {

                $imagePath = null;
                if (isset($questionData['image']) && $questionData['image'] !== null) {
                    try {
                        if (!Storage::disk('public')->exists('question-images')) {
                            Storage::disk('public')->makeDirectory('question-images');
                        }

                        $image = $questionData['image'];
                        if ($image && $image->isValid()) {
                            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                            $imagePath = $image->storeAs('question-images', $filename, 'public');
                        }
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }

                $question = $assessment->questions()->create([
                    'content' => $questionData['content'],
                    'question_type' => $questionData['question_type'],
                    'image' => $imagePath // Add image path to question
                ]);

                foreach ($questionData['options'] as $index => $optionText) {
                    $isCorrect = $questionData['question_type'] === 'single_choice'
                        ? (int)$questionData['correct_answer'] === $index
                        : (isset($questionData['correct_answer']) && is_array($questionData['correct_answer']) && in_array($index, $questionData['correct_answer']));

                    $question->options()->create([
                        'content' => $optionText,
                        'is_correct' => $isCorrect
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('assessments.index')
                ->with('success', 'Assessment berhasil dibuat.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Mohon periksa kembali data yang dimasukkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function regenerateToken(Assessment $assessment)
    {
        if ($assessment->status !== 'Terbuka') {
            return back()->with('error', 'Token hanya dapat di-generate untuk assessment yang Terbuka.');
        }

        $assessment->update([
            'token' => substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6),
            'token_expires_at' => now()->addMinutes(30)
        ]);

        return back()->with('success', 'Token berhasil di-generate ulang.');
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
            'status' => 'required|in:Belum Terbuka,Terbuka,Terjawab,Selesai'
        ]);

        // Jika status berubah menjadi Terbuka, generate token
        if ($validated['status'] === 'Terbuka' && $assessment->status !== 'Terbuka') {
            $validated['token'] = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);
            $validated['token_expires_at'] = now()->addMinutes(30);
        }

        // Jika status berubah dari Terbuka ke status lain, hapus token
        if ($validated['status'] !== 'Terbuka' && $assessment->status === 'Terbuka') {
            $validated['token'] = null;
            $validated['token_expires_at'] = null;
        }

        $assessment->update($validated);

        return redirect()->route('assessments.index')
            ->with('success', 'Assessment berhasil diperbarui');
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
            'status' => 'required|in:Belum Terbuka,Terbuka,Terjawab,Selesai',
            'questions' => 'array',
            'questions.*.content' => 'required',
            'questions.*.question_type' => 'required|in:single_choice,multiple_choice',
            'questions.*.options' => 'required|array',
            'questions.*.options.*.content' => 'required',
            'questions.*.options.*.is_correct' => 'required|boolean',
            'questions.*.image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048'

        ]);

        $assessment = Assessment::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'user_id' => Auth::id()
        ]);

        if (isset($validated['questions'])) {
            foreach ($validated['questions'] as $questionData) {

                if (isset($questionData['image']) && $questionData['image'] !== null) {
                    try {
                        if (!Storage::disk('public')->exists('question-images')) {
                            Storage::disk('public')->makeDirectory('question-images');
                        }

                        $image = $questionData['image'];
                        if ($image && $image->isValid()) {
                            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                            $imagePath = $image->storeAs('question-images', $filename, 'public');
                        }
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }

                $question = $assessment->questions()->create([
                    'content' => $questionData['content'],
                    'question_type' => $questionData['question_type'],
                    'image' => $imagePath
                ]);

                foreach ($questionData['options'] as $optionData) {
                    $question->options()->create([
                        'content' => $optionData['content'],
                        'is_correct' => $optionData['is_correct']
                    ]);
                }
            }
        }

        $assessment->load(['questions.options']);

        return response()->json([
            'code' => 200,
            'message' => 'Assessment created successfully',
            'data' => $assessment
        ]);
    }

    public function show(Assessment $assessment)
    {
        // Load relationships
        $assessment->load(['questions.options']);

        // Get unique users who answered this assessment
        $respondents = Answer::whereHas('question', function ($query) use ($assessment) {
            $query->where('assessment_id', $assessment->id);
        })
            ->with('user')
            ->select('user_id')
            ->distinct()
            ->get()
            ->map(function ($answer) {
                return $answer->user->name;
            });

        // Fix status update when there are respondents
        if ($respondents->count() > 0) {
            $assessment->update(['status' => 'Terjawab']);
        }

        return view('admin.assessments.show', [
            'assessment' => $assessment,
            'respondents' => str_replace(['[', ']', '"'], '', $respondents)
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
            'status' => 'required|in:Belum Terbuka,Terbuka,Terjawab,Selesai'
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

<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssessmentRequest;
use App\Http\Resources\AnswerResource;
use App\Http\Resources\AssessmentResource;
use App\Models\Answer;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\User;
use App\Models\UserAssessment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.assessments.index', compact('assessments', 'pendingUsers'));
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
        $pendingUsers = User::where('status', 'Pending')->count();

        // Return view with assessments and filters
        return view('admin.assessments.index', compact('assessments', 'filters', 'pendingUsers'));
    }

    public function create()
    {
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.assessments.create', compact('pendingUsers'));
    }

    public function store(AssessmentRequest $request)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $tokenData = [
                'token' => null,
                'token_expires_at' => null
            ];

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
                'timer' => $validated['timer'],
                'token' => $tokenData['token'],
                'token_expires_at' => $tokenData['token_expires_at']
            ]);

            foreach ($validated['questions'] as $questionData) {
                $question = $assessment->questions()->create([
                    'content' => $questionData['content'],
                    'question_type' => $questionData['question_type'],
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
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['error' => 'No image file uploaded'], 400);
        }

        try {
            $file = $request->file('image');

            // Validate the uploaded file
            $validator = validator(['image' => $file], [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            // Generate a unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

            // Store the file in the public disk
            $path = $file->storeAs('uploads/images', $filename, 'public');

            // Generate the URL for the stored image
            $url = Storage::url($path);

            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to upload image'], 500);
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
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.assessments.edit', compact('assessment', 'pendingUsers'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|max:255',
            'status' => 'required|in:Belum Terbuka,Terbuka,Terjawab,Selesai',
            'timer' => 'nullable'
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
        $userId = Auth::id();
        $assessments = Assessment::latest()->get();
        $userAssessments = UserAssessment::where('user_id', $userId)->get();

        if ($userId) {
            return response()->json([
                'code' => 200,
                'message' => 'Assessment retrieved successfully',
                'data' => $assessments->map(function ($assessment) use ($userAssessments) {
                    return [
                        'id' => $assessment->id,
                        'title' => $assessment->title,
                        'category' => $assessment->category,
                        'status' => $assessment->status,
                        'token' => $assessment->token,
                        'created_at' => $assessment->created_at,
                        'updated_at' => $assessment->updated_at,
                        'token_expires_at' => $assessment->token_expires_at,
                        'timer' => $assessment->timer,
                        'done' => $userAssessments->contains('assessment_id', $assessment->id) ? 1 : 0,
                    ];
                })
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Assessment retrieved successfully',
            'data' => $assessments
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
        $pendingUsers = User::where('status', 'Pending')->count();

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

        $respondentCount = $respondents->count();

        return view('admin.assessments.show', [
            'assessment' => $assessment,
            'respondents' => str_replace(['[', ']', '"'], '', $respondents)
        ], compact('pendingUsers', 'respondentCount'));
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

    public function startAssessment(Request $request, Assessment $assessment)
    {
        // Validasi token
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        // Periksa kevalidan token
        if ($validated['token'] !== $assessment->token) {
            Log::warning('Invalid assessment token', [
                'provided' => $validated['token'],
                'expected' => $assessment->token
            ]);
            return response()->json([
                'message' => 'Invalid assessment token'
            ], 403);
        }

        // Validasi kedaluwarsa token
        if (now()->gt($assessment->token_expires_at)) {
            Log::warning('Token expired', [
                'expires_at' => $assessment->token_expires_at,
                'current_time' => now()
            ]);
            return response()->json([
                'message' => 'The assessment token has expired'
            ], 403);
        }

        // Get authenticated user
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        // Check if user has already answered questions in this assessment
        $hasCompleted = Answer::whereHas('question', function ($query) use ($assessment) {
            $query->where('assessment_id', $assessment->id);
        })
            ->where('user_id', $userId)
            ->exists();

        if ($hasCompleted) {
            return response()->json([
                'message' => 'You have already completed this assessment'
            ], 403);
        }

        try {
            // Penanganan nilai timer yang lebih baik
            $assessmentDurationMinutes = 0;
            $assessmentDurationFormatted = '00:00:00';

            if (!empty($assessment->timer)) {
                // Periksa apakah timer dalam format H:i:s
                if (preg_match('/^\d{1,2}:\d{1,2}:\d{1,2}$/', $assessment->timer)) {
                    // Timer dalam format H:i:s
                    $parts = explode(':', $assessment->timer);
                    $assessmentDurationMinutes = (intval($parts[0]) * 60) + intval($parts[1]);
                    $assessmentDurationFormatted = $assessment->timer;
                } else {
                    // Coba konversi ke integer
                    $assessmentDurationMinutes = intval($assessment->timer);

                    // Format untuk ditampilkan
                    $hours = floor($assessmentDurationMinutes / 60);
                    $minutes = $assessmentDurationMinutes % 60;
                    $assessmentDurationFormatted = sprintf('%02d:%02d:00', $hours, $minutes);
                }
            }

            // Set session untuk assessment ini dengan waktu kedaluwarsa
            $sessionKey = 'assessment_' . $assessment->id . '_user_' . $userId;
            $expiryTime = now()->addMinutes($assessmentDurationMinutes);

            $sessionData = [
                'user_id' => $userId,
                'assessment_id' => $assessment->id,
                'started_at' => now(),
                'expires_at' => $expiryTime
            ];

            // Simpan data sesi ke cache dengan waktu kedaluwarsa yang tepat
            $cacheDuration = $assessmentDurationMinutes > 0 ? $assessmentDurationMinutes * 60 : 60; // minimal 1 menit
            Cache::put($sessionKey, $sessionData, $cacheDuration);

            // Ambil pertanyaan untuk assessment ini
            $questions = Question::where('assessment_id', $assessment->id)
                ->with('options:id,question_id,content')
                ->get(['id', 'content', 'question_type', 'assessment_id']);

            return response()->json([
                'message' => 'Assessment session started successfully',
                'expires_at' => $expiryTime,
                'timer' => $assessmentDurationFormatted,
                'questions' => $questions
            ]);
        } catch (\Exception $e) {
            // Log exception yang terjadi
            Log::error('Exception in startAssessment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'assessment_id' => $assessment->id
            ]);

            return response()->json([
                'message' => 'Error starting assessment: ' . $e->getMessage()
            ], 500);
        }
    }
}

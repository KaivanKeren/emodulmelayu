<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use App\Http\Resources\AnswerResource;
use App\Models\Assessment;
use App\Models\User;
use App\Models\UserAssessment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnswerController extends Controller
{
    public function show(Assessment $assessment)
    {
        $pendingUsers = User::where('status', 'Pending')->count();

        // Load assessment with questions and options
        $assessment->load(['questions.options']);

        // Get total number of questions for score calculation
        $totalQuestions = $assessment->questions->count();

        // Get answers grouped by user with calculated scores
        $respondents = Answer::whereHas('question', function ($query) use ($assessment) {
            $query->where('assessment_id', $assessment->id);
        })
            ->with(relations: ['user', 'user.school', 'question.options', 'option'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($userAnswers) use ($totalQuestions): array {
                $user = $userAnswers->first()->user;

                // Group answers by question to properly calculate scores
                $questionGroups = $userAnswers->groupBy('question_id');

                $totalScore = 0;
                foreach ($questionGroups as $questionId => $answers) {
                    $question = $answers->first()->question;
                    $selectedOptions = $answers->pluck('option');

                    if ($question->question_type === 'single_choice') {
                        // For single choice questions
                        $isCorrect = $selectedOptions->contains(function ($option) {
                            return $option->is_correct;
                        });
                        $questionScore = $isCorrect ? (100 / $totalQuestions) : 0;
                    } else {
                        // For multiple choice questions
                        $correctOptions = $question->options->where('is_correct', true);
                        $totalCorrectOptions = $correctOptions->count();

                        if ($selectedOptions->count() > $totalCorrectOptions) {
                            $questionScore = 0;
                        } else {
                            $correctlySelected = $selectedOptions->where('is_correct', true)->count();
                            $incorrectlySelected = $selectedOptions->where('is_correct', false)->count();

                            // Calculate base score
                            $baseScore = $correctlySelected / $totalCorrectOptions;

                            // Calculate penalty
                            $totalOptions = $question->options->count();
                            $penaltyPerWrong = 1 / ($totalOptions - $totalCorrectOptions);
                            $penalty = $incorrectlySelected * $penaltyPerWrong;

                            // Calculate final question score
                            $questionScore = max(0, $baseScore - $penalty) * (100 / $totalQuestions);
                        }
                    }

                    $totalScore += $questionScore;
                }

                // Get answered questions count
                $answeredQuestions = $questionGroups->count();

                return [
                    'user' => $user,
                    'school' => $user->school ? $user->school->name : 'No School',
                    'total_score' => round($totalScore, 2),
                    'answered_questions' => $answeredQuestions,
                    'completion_percentage' => round(($answeredQuestions / $totalQuestions) * 100, 2),
                    'questions_detail' => $questionGroups->map(function ($answers) {
                        return [
                            'question_id' => $answers->first()->question_id,
                            'question_type' => $answers->first()->question->question_type,
                            'selected_options' => $answers->pluck('option.id')->toArray(),
                            'score' => $answers->first()->score
                        ];
                    })
                ];
            });

        return view('admin.answers.index', compact('assessment', 'respondents', 'pendingUsers', 'totalQuestions'));
    }

    public function showApi(Assessment $assessment): JsonResponse
    {
        $assessment->load(['questions.options']);

        // Get total number of questions for score calculation
        $totalQuestions = $assessment->questions->count();

        // Get answers grouped by user with calculated scores
        $respondents = Answer::whereHas('question', function ($query) use ($assessment) {
            $query->where('assessment_id', $assessment->id);
        })
            ->with(['user', 'user.school', 'question.options', 'option'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($userAnswers) use ($totalQuestions) {
                $user = $userAnswers->first()->user;

                // Group answers by question to properly calculate scores
                $questionGroups = $userAnswers->groupBy('question_id');

                $totalScore = 0;
                foreach ($questionGroups as $questionId => $answers) {
                    $question = $answers->first()->question;
                    $selectedOptions = $answers->pluck('option');

                    if ($question->question_type === 'single_choice') {
                        // For single choice questions
                        $isCorrect = $selectedOptions->contains(function ($option) {
                            return $option->is_correct;
                        });
                        $questionScore = $isCorrect ? (100 / $totalQuestions) : 0;
                    } else {
                        // For multiple choice questions
                        $correctOptions = $question->options->where('is_correct', true);
                        $totalCorrectOptions = $correctOptions->count();

                        if ($selectedOptions->count() > $totalCorrectOptions) {
                            $questionScore = 0;
                        } else {
                            $correctlySelected = $selectedOptions->where('is_correct', true)->count();
                            $incorrectlySelected = $selectedOptions->where('is_correct', false)->count();

                            // Calculate base score
                            $baseScore = $correctlySelected / $totalCorrectOptions;

                            // Calculate penalty
                            $totalOptions = $question->options->count();
                            $penaltyPerWrong = 1 / ($totalOptions - $totalCorrectOptions);
                            $penalty = $incorrectlySelected * $penaltyPerWrong;

                            // Calculate final question score
                            $questionScore = max(0, $baseScore - $penalty) * (100 / $totalQuestions);
                        }
                    }

                    $totalScore += $questionScore;
                }

                // Get answered questions count
                $answeredQuestions = $questionGroups->count();

                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'school' => $user->school ? $user->school->name : 'No School',
                    ],
                    'total_score' => round($totalScore, 2),
                    'answered_questions' => $answeredQuestions,
                    'completion_percentage' => round(($answeredQuestions / $totalQuestions) * 100, 2),
                    'questions_detail' => $questionGroups->map(function ($answers) {
                        return [
                            'question_id' => $answers->first()->question_id,
                            'question_type' => $answers->first()->question->question_type,
                            'selected_options' => $answers->pluck('option.id')->toArray(),
                        ];
                    })
                ];
            })->values();

        return response()->json([
            'code' => 200,
            'message' => 'All answers retrieved successfully',
            'data' => [
                'assessment' => [
                    'id' => $assessment->id,
                    'title' => $assessment->title,
                    'description' => $assessment->description,
                    'total_questions' => $totalQuestions,
                ],
                'respondents' => $respondents,
                'stats' => [
                    'total_respondents' => $respondents->count(),
                ]
            ]
        ]);
    }

    public function detail(Assessment $assessment, User $user)
    {
        // Load assessment with questions and options in a single query
        $assessment->load(['questions.options']);

        // Get pending users count
        $pendingUsers = User::where('status', 'Pending')->count();

        // Get user's answers for this assessment - optimized query
        $userAnswers = Answer::with(['question.options', 'option'])
            ->whereHas('question', function ($query) use ($assessment) {
                $query->where('assessment_id', $assessment->id);
            })
            ->where('user_id', $user->id)
            ->get()
            ->groupBy('question_id');

        // Get total number of questions for score calculation
        $totalQuestions = $assessment->questions->count();

        if ($totalQuestions === 0) {
            return back()->with('error', 'This assessment has no questions.');
        }

        // Track the total score separately
        $totalScore = 0;
        $questionScores = [];

        // First pass: Calculate all question scores and store them
        foreach ($assessment->questions as $question) {
            $questionId = $question->id;
            $answers = $userAnswers->get($questionId);
            $questionScore = 0;

            // Calculate score if user has answered this question
            if ($answers && $answers->isNotEmpty()) {
                $selectedOptions = $answers->pluck('option');

                if ($question->question_type === 'single_choice') {
                    // For single choice questions
                    $isCorrect = $selectedOptions->contains(function ($option) {
                        return $option->is_correct;
                    });
                    $questionScore = $isCorrect ? (100 / $totalQuestions) : 0;
                } else {
                    // For multiple choice questions
                    $correctOptions = $question->options->where('is_correct', true);
                    $totalCorrectOptions = $correctOptions->count();

                    if ($totalCorrectOptions > 0 && $selectedOptions->isNotEmpty()) {
                        if ($selectedOptions->count() > $totalCorrectOptions) {
                            $questionScore = 0;
                        } else {
                            $correctlySelected = $selectedOptions->where('is_correct', true)->count();
                            $incorrectlySelected = $selectedOptions->where('is_correct', false)->count();

                            // Calculate base score
                            $baseScore = $correctlySelected / $totalCorrectOptions;

                            // Calculate penalty (avoid division by zero)
                            $totalOptions = $question->options->count();
                            $nonCorrectOptions = $totalOptions - $totalCorrectOptions;
                            $penaltyPerWrong = ($nonCorrectOptions > 0) ? (1 / $nonCorrectOptions) : 0;
                            $penalty = $incorrectlySelected * $penaltyPerWrong;

                            // Calculate final question score
                            $questionScore = max(0, $baseScore - $penalty) * (100 / $totalQuestions);
                        }
                    }
                }

                // Add to total score
                $totalScore += $questionScore;
            }

            // Store the score for this question
            $questionScores[$questionId] = round($questionScore, 2);
        }

        // Second pass: Prepare detailed view data
        $questionsDetail = $assessment->questions->map(function ($question) use ($userAnswers, $questionScores) {
            $questionId = $question->id;
            $answers = $userAnswers->get($questionId);

            return [
                'question' => [
                    'id' => $questionId,
                    'content' => $question->content,
                    'type' => $question->question_type,
                ],
                'is_answered' => !is_null($answers) && $answers->isNotEmpty(),
                'user_answers' => ($answers && $answers->isNotEmpty()) ? [
                    'selected_options' => $answers->map(function ($answer) {
                        return [
                            'option_id' => $answer->option->id,
                            'option_content' => $answer->option->content,
                            'is_correct' => $answer->option->is_correct,
                        ];
                    }),
                    'score' => $questionScores[$questionId],
                    'submitted_at' => optional($answers->first())->created_at?->format('Y-m-d H:i:s'),
                ] : null,
                'all_options' => $question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'content' => $option->content,
                        'is_correct' => $option->is_correct,
                    ];
                }),
            ];
        });

        // Calculate summary statistics safely
        $answeredQuestions = $questionsDetail->where('is_answered', true)->count();
        $startedAt = null;
        $completedAt = null;

        if ($userAnswers->isNotEmpty()) {
            $allAnswers = $userAnswers->flatten();
            $startedAt = $allAnswers->min('created_at');
            $completedAt = $allAnswers->max('created_at');
        }

        $summary = [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'completion_percentage' => $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0,
            'total_score' => round($totalScore, 2),
            'assessment_started_at' => $startedAt,
            'assessment_completed_at' => $completedAt,
        ];

        // Import Carbon in the controller for date handling
        if (!class_exists('Carbon\Carbon')) {
            class_alias('Illuminate\Support\Carbon', 'Carbon\Carbon');
        }

        return view('admin.answers.detail', compact('assessment', 'user', 'questionsDetail', 'summary', 'pendingUsers'));
    }

    public function exportPdf(Assessment $assessment)
    {
        // Load assessment with questions and options
        $assessment->load(['questions.options']);

        // Get total number of questions
        $totalQuestions = $assessment->questions->count();

        if ($totalQuestions === 0) {
            return back()->with('error', 'This assessment has no questions.');
        }

        // Get answers with the same calculation logic as detail method
        $respondents = Answer::whereHas('question', function ($query) use ($assessment) {
            $query->where('assessment_id', $assessment->id);
        })
            ->with(['user', 'question.options', 'option'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($userAnswers) use ($totalQuestions) {
                $user = $userAnswers->first()->user;
                $questionGroups = $userAnswers->groupBy('question_id');

                // Track the total score separately
                $totalScore = 0;
                $questionScores = [];

                // First pass: Calculate all question scores and store them
                foreach ($questionGroups as $questionId => $answers) {
                    $question = $answers->first()->question;
                    $selectedOptions = $answers->pluck('option');
                    $questionScore = 0;

                    if ($question->question_type === 'single_choice') {
                        // For single choice questions
                        $isCorrect = $selectedOptions->contains(function ($option) {
                            return $option->is_correct;
                        });
                        $questionScore = $isCorrect ? (100 / $totalQuestions) : 0;
                    } else {
                        // For multiple choice questions
                        $correctOptions = $question->options->where('is_correct', true);
                        $totalCorrectOptions = $correctOptions->count();

                        if ($totalCorrectOptions > 0 && $selectedOptions->isNotEmpty()) {
                            if ($selectedOptions->count() > $totalCorrectOptions) {
                                $questionScore = 0;
                            } else {
                                $correctlySelected = $selectedOptions->where('is_correct', true)->count();
                                $incorrectlySelected = $selectedOptions->where('is_correct', false)->count();

                                // Calculate base score
                                $baseScore = $correctlySelected / $totalCorrectOptions;

                                // Calculate penalty (avoid division by zero)
                                $totalOptions = $question->options->count();
                                $nonCorrectOptions = $totalOptions - $totalCorrectOptions;
                                $penaltyPerWrong = ($nonCorrectOptions > 0) ? (1 / $nonCorrectOptions) : 0;
                                $penalty = $incorrectlySelected * $penaltyPerWrong;

                                // Calculate final question score
                                $questionScore = max(0, $baseScore - $penalty) * (100 / $totalQuestions);
                            }
                        }
                    }

                    // Add to total score
                    $totalScore += $questionScore;

                    // Store the score for this question
                    $questionScores[$questionId] = round($questionScore, 2);
                }

                return [
                    'user' => $user,
                    'total_score' => round($totalScore, 2),
                    'answered_questions' => $questionGroups->count(),
                    'completion_percentage' => round(($questionGroups->count() / $totalQuestions) * 100, 2),
                    'questions_detail' => $questionGroups->map(function ($answers) use ($questionScores) {
                        $questionId = $answers->first()->question->id;
                        return [
                            'question' => $answers->first()->question,
                            'selected_options' => $answers->pluck('option'),
                            'score' => $questionScores[$questionId] // Use the calculated score from above
                        ];
                    })
                ];
            });

        $pdf = Pdf::loadView('admin.answers.pdf', compact('respondents', 'assessment', 'totalQuestions'));
        return $pdf->download('Jawaban_Siswa_' . $assessment->title . '.pdf');
    }

    public function apiStore(Request $request, Assessment $assessment): JsonResponse
    {
        try {
            $userId = Auth::id();
            $sessionKey = 'assessment_' . $assessment->id . '_user_' . $userId; // Include user_id in sessionKey
            $session = Cache::get($sessionKey);

            // Verify user is authenticated
            if (!$userId) {
                return response()->json([
                    'message' => 'User not authenticated'
                ], 401);
            }

            if (!$session) {
                return response()->json([
                    'message' => 'No active assessment session found. Please start the assessment first.'
                ], 403);
            }

            // Check if session has expired
            if (now()->gt($session['expires_at'])) {
                Cache::forget($sessionKey);
                return response()->json([
                    'message' => 'Your assessment session has expired'
                ], 403);
            }

            // Validate inputs
            $validated = $request->validate([
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|exists:questions,id',
                'answers.*.option_ids' => 'required|array',
                'answers.*.option_ids.*' => 'required|exists:options,id'
            ]);

            // Use transaction to ensure data integrity
            $result = DB::transaction(function () use ($validated, $assessment) {
                $savedAnswers = [];
                $errors = [];
                $userId = Auth::id();

                // Verify user is authenticated
                if (!$userId) {
                    throw new \Exception('User not authenticated');
                }

                // Process each answer
                foreach ($validated['answers'] as $answerData) {
                    $questionId = $answerData['question_id'];
                    $optionIds = $answerData['option_ids'];

                    // Verify question belongs to assessment
                    $question = Question::where('id', $questionId)
                        ->where('assessment_id', $assessment->id)
                        ->first();

                    if (!$question) {
                        $errors[] = "Question {$questionId} does not belong to this assessment";
                        continue;
                    }

                    // Check for duplicate answers
                    if (Answer::where('user_id', $userId)
                        ->where('question_id', $questionId)
                        ->exists()
                    ) {
                        $errors[] = "You have already answered question {$questionId}";
                        continue;
                    }

                    // Validate number of options selected for single choice
                    if ($question->question_type === 'single_choice' && count($optionIds) > 1) {
                        $errors[] = "Only one option can be selected for single choice question {$questionId}";
                        continue;
                    }

                    // Get and validate options
                    $options = Option::whereIn('id', $optionIds)
                        ->where('question_id', $questionId)
                        ->get();

                    // Validate if all options belong to the question
                    if ($options->count() !== count($optionIds)) {
                        $errors[] = "Invalid options provided for question {$questionId}";
                        continue;
                    }

                    // Create answers for this question
                    foreach ($options as $option) {
                        $answer = Answer::create([
                            'question_id' => $questionId,
                            'option_id' => $option->id,
                            'user_id' => $userId,
                        ]);

                        $savedAnswers[] = $answer;
                    }
                }

                return ['savedAnswers' => $savedAnswers, 'errors' => $errors];
            });

            // Clear the session after storing answers
            Cache::forget($sessionKey);

            // Get all questions for this assessment
            $assessmentQuestions = Question::where('assessment_id', $assessment->id)->pluck('id');
            $totalQuestions = $assessmentQuestions->count();

            // Get count of questions answered 
            $answeredCount = 0;
            if ($totalQuestions > 0) {
                $answeredCount = Answer::where('user_id', $userId)
                    ->whereIn('question_id', $assessmentQuestions)
                    ->distinct('question_id')
                    ->count('question_id');
            }

            // Create UserAssessment record
            $userAssessment = UserAssessment::create([
                'user_id' => $userId,
                'assessment_id' => $assessment->id,
                'total_questions' => $totalQuestions,
                'question_answered' => $answeredCount
            ]);

            // Prepare response
            $response = [
                'code' => 200,
                'message' => count($result['savedAnswers']) > 0 ? 'Answers submitted and assessment completed successfully' : 'No answers were submitted',
                'data' => [
                    'answers' => AnswerResource::collection(collect($result['savedAnswers'])),
                    'assessment_summary' => $userAssessment
                ]
            ];

            if (count($result['errors']) > 0) {
                $response['errors'] = $result['errors'];
            }

            return response()->json($response);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors
            Log::error('Database exception in apiStore', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'assessment_id' => $assessment->id
            ]);

            return response()->json([
                'message' => 'Database error occurred'
            ], 500);
        } catch (\Exception $e) {
            // Handle all other exceptions
            Log::error('Exception in apiStore', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'assessment_id' => $assessment->id
            ]);

            return response()->json([
                'message' => 'An error occurred while processing your request'
            ], 500);
        }
    }

    public function apiIndex(Request $request, Assessment $assessment, Question $question)
    {
        // Validate token
        $validated = $request->validate([
            'token' => 'required|string'
        ]);

        if ($validated['token'] !== $assessment->token) {
            return response()->json([
                'message' => 'Invalid assessment token'
            ], 403);
        }

        if ($question->assessment_id !== $assessment->id) {
            return response()->json([
                'message' => 'Question does not belong to this assessment'
            ], 404);
        }

        $answers = Answer::where('question_id', $question->id)
            ->where('user_id', Auth::id())
            ->with('option')
            ->get();

        return AnswerResource::collection($answers);
    }

    public function apiShow(Request $request, Assessment $assessment, Question $question, Answer $answer)
    {
        // Validate token
        $validated = $request->validate([
            'token' => 'required|string'
        ]);

        if ($validated['token'] !== $assessment->token) {
            return response()->json([
                'message' => 'Invalid assessment token'
            ], 403);
        }

        if ($question->assessment_id !== $assessment->id) {
            return response()->json([
                'message' => 'Question does not belong to this assessment'
            ], 404);
        }

        if ($answer->user_id !== Auth::id() || $answer->question_id !== $question->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return new AnswerResource($answer->load('option'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use App\Http\Resources\AnswerResource;
use App\Models\Assessment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
        // Load assessment with questions and options
        $assessment->load(['questions.options']);

        $pendingUsers = User::where('status', 'Pending')->count();

        // Get user's answers for this assessment
        $userAnswers = Answer::whereHas('question', function ($query) use ($assessment) {
            $query->where('assessment_id', $assessment->id);
        })
            ->where('user_id', $user->id)
            ->with(['question.options', 'option'])
            ->get()
            ->groupBy('question_id');

        // Get total number of questions for score calculation
        $totalQuestions = $assessment->questions->count();

        // Calculate total score using the same logic as show function
        $totalScore = 0;
        foreach ($userAnswers as $questionId => $answers) {
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

        // Prepare detailed answer data
        $questionsDetail = $assessment->questions->map(function ($question) use ($userAnswers) {
            $answers = $userAnswers->get($question->id);

            return [
                'question' => [
                    'id' => $question->id,
                    'content' => $question->content,
                    'type' => $question->question_type,
                ],
                'is_answered' => !is_null($answers),
                'user_answers' => $answers ? [
                    'selected_options' => $answers->map(function ($answer) {
                        return [
                            'option_id' => $answer->option->id,
                            'option_content' => $answer->option->content,
                            'is_correct' => $answer->option->is_correct,
                        ];
                    }),
                    'score' => $answers->first()->score,
                    'submitted_at' => $answers->first()->created_at->format('Y-m-d H:i:s'),
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

        // Calculate summary statistics
        $answeredQuestions = $userAnswers->count();

        $summary = [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'completion_percentage' => round(($answeredQuestions / $totalQuestions) * 100, 2),
            'total_score' => round($totalScore, 2),
            'assessment_started_at' => $userAnswers->flatten()->min('created_at'),
            'assessment_completed_at' => $userAnswers->flatten()->max('created_at'),
        ];

        return view('admin.answers.detail', compact('assessment', 'user', 'questionsDetail', 'summary', 'pendingUsers'));
    }

    public function exportPdf(Assessment $assessment)
    {
        // Load assessment with questions and options
        $assessment->load(['questions.options']);

        // Get total number of questions
        $totalQuestions = $assessment->questions->count();

        // Get answers with the same calculation logic as show method
        $respondents = Answer::whereHas('question', function ($query) use ($assessment) {
            $query->where('assessment_id', $assessment->id);
        })
            ->with(['user', 'question.options', 'option'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($userAnswers) use ($totalQuestions) {
                $user = $userAnswers->first()->user;
                $questionGroups = $userAnswers->groupBy('question_id');

                $totalScore = 0;
                foreach ($questionGroups as $questionId => $answers) {
                    $question = $answers->first()->question;
                    $selectedOptions = $answers->pluck('option');

                    if ($question->question_type === 'single_choice') {
                        $isCorrect = $selectedOptions->contains(function ($option) {
                            return $option->is_correct;
                        });
                        $questionScore = $isCorrect ? (100 / $totalQuestions) : 0;
                    } else {
                        $correctOptions = $question->options->where('is_correct', true);
                        $totalCorrectOptions = $correctOptions->count();

                        if ($selectedOptions->count() > $totalCorrectOptions) {
                            $questionScore = 0;
                        } else {
                            $correctlySelected = $selectedOptions->where('is_correct', true)->count();
                            $incorrectlySelected = $selectedOptions->where('is_correct', false)->count();

                            $baseScore = $correctlySelected / $totalCorrectOptions;
                            $totalOptions = $question->options->count();
                            $penaltyPerWrong = 1 / ($totalOptions - $totalCorrectOptions);
                            $penalty = $incorrectlySelected * $penaltyPerWrong;

                            $questionScore = max(0, $baseScore - $penalty) * (100 / $totalQuestions);
                        }
                    }

                    $totalScore += $questionScore;
                }

                return [
                    'user' => $user,
                    'total_score' => round($totalScore, 2),
                    'answered_questions' => $questionGroups->count(),
                    'completion_percentage' => round(($questionGroups->count() / $totalQuestions) * 100, 2),
                    'questions_detail' => $questionGroups->map(function ($answers) {
                        return [
                            'question' => $answers->first()->question,
                            'selected_options' => $answers->pluck('option'),
                            'score' => $answers->first()->score
                        ];
                    })
                ];
            });

        $pdf = Pdf::loadView('admin.answers.pdf', compact('respondents', 'assessment', 'totalQuestions'));
        return $pdf->download('Jawaban_Siswa_' . $assessment->title . '.pdf');
    }

    public function apiStore(Request $request, Assessment $assessment, Question $question)
    {
        if ($question->assessment_id !== $assessment->id) {
            return response()->json([
                'message' => 'Question does not belong to this assessment'
            ], 404);
        }

        // Validate token and inputs
        $validated = $request->validate([
            'token' => 'required|string',
            'option_ids' => 'required|array',
            'option_ids.*' => 'required|exists:options,id'
        ]);

        // Check token validity
        if ($validated['token'] !== $assessment->token) {
            return response()->json([
                'message' => 'Invalid assessment token'
            ], 403);
        }

        // Validate token expiration
        if (now()->gt($assessment->token_expires_at)) {
            return response()->json([
                'message' => 'The assessment token has expired'
            ], 403);
        }

        // Get and validate options
        $options = Option::whereIn('id', $validated['option_ids'])
            ->where('question_id', $question->id)
            ->get();

        // Validate selected options count
        if ($question->question_type === 'single_choice' && count($validated['option_ids']) > 1) {
            return response()->json([
                'message' => 'Only one option can be selected for single choice questions'
            ], 422);
        }

        // Validate if all options belong to the question
        if ($options->count() !== count($validated['option_ids'])) {
            return response()->json([
                'message' => 'Invalid options provided for this question'
            ], 422);
        }

        // Check for duplicate answers
        if (Answer::where('user_id', Auth::id())
            ->where('question_id', $question->id)
            ->exists()
        ) {
            return response()->json([
                'message' => 'You have already answered this question'
            ], 422);
        }

        // Calculate score
        $score = $this->apiCalculateScore($question, $options);

        // Create answers
        $answers = [];
        foreach ($options as $option) {
            $answers[] = Answer::create([
                'question_id' => $question->id,
                'option_id' => $option->id,
                'user_id' => Auth::id(),
                'score' => $score
            ]);
        }

        return response()->json([
            'message' => 'Answer submitted successfully',
            'score' => $score,
            'data' => AnswerResource::collection(collect($answers))
        ]);
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

    public function apiUpdate(Request $request, Assessment $assessment, Question $question)
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

        Answer::where('question_id', $question->id)
            ->where('user_id', Auth::id())
            ->delete();

        return $this->apiStore($request, $assessment, $question);
    }

    public function apiDestroy(Request $request, Assessment $assessment, Question $question)
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

        $deleted = Answer::where('question_id', $question->id)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json([
            'message' => 'Answer deleted successfully',
            'deleted_count' => $deleted
        ]);
    }
}

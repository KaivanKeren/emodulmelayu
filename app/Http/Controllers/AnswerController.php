<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use App\Http\Resources\AnswerResource;
use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;

class AnswerController extends Controller
{
    public function apiStore(Request $request, Assessment $assessment, Question $question)
    {
        if ($question->assessment_id !== $assessment->id) {
            return response()->json([
                'message' => 'Question does not belong to this assessment'
            ], 404);
        }

        // Validate request
        $validated = $request->validate([
            'option_ids' => 'required|array',
            'option_ids.*' => 'required|exists:options,id'
        ]);

        // Verify all options belong to the question
        $options = Option::whereIn('id', $validated['option_ids'])
            ->where('question_id', $question->id)
            ->get();

        if ($options->count() !== count($validated['option_ids'])) {
            return response()->json([
                'message' => 'Invalid options provided for this question'
            ], 422);
        }

        // Check if answer already exists for this user and question
        $existingAnswers = Answer::where('user_id', Auth::id())
            ->where('question_id', $question->id)
            ->exists();

        if ($existingAnswers) {
            return response()->json([
                'message' => 'You have already answered this question'
            ], 422);
        }

        // Calculate score based on question type and selected options
        $score = $this->apiCalculateScore($question, $options);

        // Create answers for each selected option
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

    private function apiCalculateScore(Question $question, $selectedOptions)
    {
        $correctOptions = Option::where('question_id', $question->id)
            ->where('is_correct', true)
            ->get();

        $selectedCorrectOptions = $selectedOptions->filter(function ($option) {
            return $option->is_correct;
        });

        if ($question->question_type === 'single_choice') {
            // For single choice, return full score if the one selected option is correct
            return ($selectedOptions->count() === 1 && $selectedCorrectOptions->count() === 1) ? 1 : 0;
        } else {
            // For multiple choice, calculate partial credit
            $totalOptions = $correctOptions->count();
            $correctlySelected = $selectedCorrectOptions->count();
            $incorrectlySelected = $selectedOptions->count() - $correctlySelected;

            // Calculate score based on correct selections minus penalties for incorrect selections
            $score = ($correctlySelected / $totalOptions) - ($incorrectlySelected * 0.25);
            return max(0, $score); // Ensure score doesn't go below 0
        }
    }

    public function apiIndex(Assessment $assessment, Question $question)
    {
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

    public function apiShow(Assessment $assessment, Question $question, Answer $answer)
    {
        // Verify the answer belongs to the authenticated user and question
        if ($question->assessment_id !== $assessment->id) {
            return response()->json([
                'message' => 'Question does not belong to this assessment'
            ], 404);
        }

        // Verify the answer belongs to the authenticated user and question
        if ($answer->user_id !== Auth::id() || $answer->question_id !== $question->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return new AnswerResource($answer->load('option'));
    }

    public function apiUpdate(Request $request, Assessment $assessment, Question $question)
    {
        // First delete existing answers
        if ($question->assessment_id !== $assessment->id) {
            return response()->json([
                'message' => 'Question does not belong to this assessment'
            ], 404);
        }

        // First delete existing answers
        Answer::where('question_id', $question->id)
            ->where('user_id', Auth::id())
            ->delete();

        // Then create new answer using store method
        return $this->store($request, $assessment, $question);
    }

    public function apiDestroy(Assessment $assessment, Question $question)
    {
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

    public function apiGetResults(Assessment $assessment, Question $question)
    {
        if ($question->assessment_id !== $assessment->id) {
            return response()->json([
                'message' => 'Question does not belong to this assessment'
            ], 404);
        }

        $answers = Answer::where('question_id', $question->id)
            ->where('user_id', Auth::id())
            ->with(['option', 'question'])
            ->get();

        $correctOptions = Option::where('question_id', $question->id)
            ->where('is_correct', true)
            ->get();

        return response()->json([
            'answers' => AnswerResource::collection($answers),
            'score' => $answers->first() ? $answers->first()->score : 0,
            'correct_options' => $correctOptions,
            'question_type' => $question->question_type
        ]);
    }
}

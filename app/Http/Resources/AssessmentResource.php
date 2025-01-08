<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // Add other assessment fields here
            'questions' => $this->whenLoaded('questions', function() {
                return $this->questions->map(function($question) {
                    return [
                        'id' => $question->id,
                        'content' => $question->content,
                        'options' => $question->options->map(function($option) {
                            return [
                                'id' => $option->id,
                                'content' => $option->content,
                                'is_correct' => $option->is_correct,
                            ];
                        }),
                        'created_at' => $question->created_at,
                        'updated_at' => $question->updated_at,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

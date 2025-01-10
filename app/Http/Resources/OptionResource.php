<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $showCorrectness = false;
        
        // Check if user is authenticated
        if (auth()->check() && $this->question) {
            // Show correctness if user has already answered
            $showCorrectness = $this->question->answers()
                ->where('user_id', auth()->id())
                ->exists();
        }

        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'content' => $this->content,
            'is_correct' => $this->when($showCorrectness, $this->is_correct),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

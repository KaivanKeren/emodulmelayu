<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'option_id' => $this->option_id,
            'user_id' => $this->user_id,
            'score' => $this->score,
            'option' => new OptionResource($this->whenLoaded('option')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

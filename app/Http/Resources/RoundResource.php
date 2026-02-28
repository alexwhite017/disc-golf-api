<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'played_at' => $this->played_at?->format('Y-m-d'),
            'notes' => $this->notes,
            'total_score' => $this->when($this->relationLoaded('scores'), fn () => $this->total_score),
            'score_vs_par' => $this->when($this->relationLoaded('scores'), fn () => $this->score_vs_par),
            'course' => new CourseResource($this->whenLoaded('course')),
            'scores' => ScoreResource::collection($this->whenLoaded('scores')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

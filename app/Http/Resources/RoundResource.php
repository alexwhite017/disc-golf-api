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
            'course' => new CourseResource($this->whenLoaded('course')),
            'scores' => ScoreResource::collection($this->whenLoaded('scores')),
            'players' => UserResource::collection($this->whenLoaded('players')),
            'player_totals' => $this->when(
                $this->relationLoaded('scores') && $this->relationLoaded('players'),
                function () {
                    $totals = new \stdClass();
                    foreach ($this->players as $player) {
                        $key = (string) $player->id;
                        $totals->$key = [
                            'total_score' => $this->totalScoreForUser($player->id) ?: null,
                            'score_vs_par' => $this->scoreVsParForUser($player->id),
                        ];
                    }
                    return $totals;
                }
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

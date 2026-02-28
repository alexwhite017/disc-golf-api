<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'round_id' => $this->round_id,
            'hole_id' => $this->hole_id,
            'strokes' => $this->strokes,
            'hole' => new HoleResource($this->whenLoaded('hole')),
        ];
    }
}

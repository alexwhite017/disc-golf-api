<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'number' => $this->number,
            'par' => $this->par,
            'distance_feet' => $this->distance_feet,
            'notes' => $this->notes,
        ];
    }
}

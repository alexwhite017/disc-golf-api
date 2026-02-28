<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'brand' => $this->brand,
            'name' => $this->name,
            'type' => $this->type,
            'weight_grams' => $this->weight_grams,
            'color' => $this->color,
            'notes' => $this->notes,
            'is_in_bag' => $this->is_in_bag,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

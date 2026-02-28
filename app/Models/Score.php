<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'round_id',
        'hole_id',
        'strokes',
    ];

    protected function casts(): array
    {
        return [
            'strokes' => 'integer',
        ];
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function hole(): BelongsTo
    {
        return $this->belongsTo(Hole::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'played_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'played_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function getTotalScoreAttribute(): int
    {
        return $this->scores->sum('strokes');
    }

    public function getScoreVsParAttribute(): ?int
    {
        if ($this->scores->isEmpty()) {
            return null;
        }

        $totalStrokes = $this->scores->sum('strokes');
        $totalPar = $this->scores->sum(fn ($score) => $score->hole->par ?? 0);

        return $totalPar > 0 ? $totalStrokes - $totalPar : null;
    }
}

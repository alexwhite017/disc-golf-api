<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'round_players')->withTimestamps();
    }

    public function totalScoreForUser(int $userId): int
    {
        return $this->scores->where('user_id', $userId)->sum('strokes');
    }

    public function scoreVsParForUser(int $userId): ?int
    {
        $userScores = $this->scores->where('user_id', $userId);

        if ($userScores->isEmpty()) {
            return null;
        }

        $totalStrokes = $userScores->sum('strokes');
        $totalPar = $userScores->sum(fn ($score) => $score->hole->par ?? 0);

        return $totalPar > 0 ? $totalStrokes - $totalPar : null;
    }
}

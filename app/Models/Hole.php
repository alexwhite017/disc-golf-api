<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hole extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'number',
        'par',
        'distance_feet',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'par' => 'integer',
            'distance_feet' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }
}

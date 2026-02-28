<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'city',
        'state',
        'country',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function holes(): HasMany
    {
        return $this->hasMany(Hole::class)->orderBy('number');
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }
}

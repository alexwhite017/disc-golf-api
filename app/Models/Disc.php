<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disc extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand',
        'name',
        'type',
        'weight_grams',
        'color',
        'notes',
        'is_in_bag',
    ];

    protected function casts(): array
    {
        return [
            'weight_grams' => 'decimal:1',
            'is_in_bag' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lottery extends Model
{
    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
        'capacity',
        'price',
        'winner_count',
        'location',
        'status',
        'drawn_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'drawn_at' => 'datetime',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(LotteryEntry::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(LotteryWinner::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}

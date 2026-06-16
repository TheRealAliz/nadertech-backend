<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetCode extends Model
{
    protected $fillable = [
        'user_id',
        'code_hash',
        'reset_token_hash',
        'expires_at',
        'verified_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getExpiresAtTimestampMs(): int
    {
        return (new Carbon($this->expires_at))->getTimestampMs();
    }

    public function getExpiresIn(): int
    {
        return now()->diffInSeconds($this->expires_at);
    }
}

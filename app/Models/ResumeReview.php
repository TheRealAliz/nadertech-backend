<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeReview extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'position',
        'position_en',
        'avatar',
        'description',
        'description_en',
        'resume_id',
    ];

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}

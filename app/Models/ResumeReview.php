<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeReview extends Model
{
    protected $fillable = [
        'name',
        'position',
        'avatar',
        'description',
        'resume_id',
    ];

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resumes::class);
    }
}

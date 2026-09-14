<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeImage extends Model
{
    protected $fillable = [
        'resume_id',
        'image',
        'alt',
        'alt_en',
        'sort_order',
    ];

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}

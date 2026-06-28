<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Resume extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_published',
        'category_id',
        'review_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectService::class);
    }

    public function images()
    {
        return $this->hasMany(ResumeImage::class)->orderBy('sort_order');
    }

    public function review(): HasOne
    {
        return $this->hasOne(ResumeReview::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}

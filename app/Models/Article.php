<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $fillable = [
        'admin_id',
        'title',
        'slug',
        'content',
        'thumbnail',
        'thumbnail_alt',
        'meta_title',
        'meta_description',
        'views_count',
        'status',
        'published_at',
    ];

    protected $casts = [
        'views_count' => 'integer',
        'published_at' => 'datetime',
        'status' => ArticleStatus::class,
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function scopePublishedLatest(Builder $query): Builder
    {
        return $query->orderByDesc('published_at');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ArticleStatus::PUBLISHED->value);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', ArticleStatus::ARCHIVED->value);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
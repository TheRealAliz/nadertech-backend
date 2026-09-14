<?php

namespace App\Http\Resources\Articles;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_en' => $this->title_en,
            'slug' => $this->slug,
            'content' => $this->content,
            'content_en' => $this->content_en,
            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,
            'thumbnail_alt' => $this->thumbnail_alt,
            'thumbnail_alt_en' => $this->thumbnail_alt_en,
            'views_count' => $this->views_count,
            'published_at' => $this->published_at,
        ];
    }
}

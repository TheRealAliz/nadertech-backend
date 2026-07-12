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
            'slug' => $this->slug,
            'content' => $this->content,
            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,
            'thumbnail_alt' => $this->thumbnail_alt,
            'views_count' => $this->views_count,
            'published_at' => $this->published_at,
        ];
    }
}

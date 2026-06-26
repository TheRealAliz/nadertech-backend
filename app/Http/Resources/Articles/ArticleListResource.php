<?php

namespace App\Http\Resources\Articles;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ArticleListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'thumbnail' => $this->thumbnail ? Storage::disk($this->thumbnail) : null,
            'thumbnail_alt' => $this->thumbnail_alt,
            'views_count' => $this->views_count,
            'published_at' => $this->published_at,
        ];
    }
}

<?php

namespace App\Http\Resources\Admin\Articles;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'admin_id' => $this->admin_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,
            'thumbnail_alt' => $this->thumbnail_alt,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'views_count' => $this->views_count,

            // Enum-friendly output
            'status' => $this->status,

            'published_at' => $this->published_at,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
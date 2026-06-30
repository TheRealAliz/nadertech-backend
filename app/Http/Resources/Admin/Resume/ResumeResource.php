<?php

namespace App\Http\Resources\Admin\Resume;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_published' => (bool) $this->is_published,

            'category' => $this->whenLoaded('category')->title,

            'review' => new ResumeReviewResource($this->whenLoaded('review')),

            'images' => ResumeImageResource::collection($this->whenLoaded('images')),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
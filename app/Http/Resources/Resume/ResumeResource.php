<?php

namespace App\Http\Resources\Resume;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'title_en' => $this->title_en,
            'slug' => $this->slug,
            'description' => $this->description,
            'description_en' => $this->description_en,

            'category' => $this->whenLoaded('category')?->title,
            'category_en' => $this->whenLoaded('category')?->title_en,

            'review' => new ResumeReviewResource($this->whenLoaded('review')),

            'images' => ResumeImageResource::collection($this->whenLoaded('images')),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}

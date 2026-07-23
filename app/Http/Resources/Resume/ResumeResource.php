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
            'slug' => $this->slug,
            'description' => $this->description,

            'category' => $this->whenLoaded('category')?->title,

            'review' => new ResumeReviewResource($this->whenLoaded('review')),

            'images' => ResumeImageResource::collection($this->whenLoaded('images')),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
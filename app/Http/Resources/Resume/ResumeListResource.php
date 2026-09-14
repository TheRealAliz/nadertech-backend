<?php

namespace App\Http\Resources\Resume;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'title_en' => $this->title_en,
            'slug' => $this->slug,
            'cover' => new ResumeImageResource($this->whenLoaded('firstImage')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}

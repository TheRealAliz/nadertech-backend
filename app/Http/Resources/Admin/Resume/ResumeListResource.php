<?php

namespace App\Http\Resources\Admin\Resume;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'cover' => new ResumeImageResource($this->whenLoaded('firstImage')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}

<?php

namespace App\Http\Resources\Admin\Resume;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'name_en' => $this->name_en,
            'position' => $this->position,
            'position_en' => $this->position_en,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'description' => $this->description,
            'description_en' => $this->description_en,
        ];
    }
}

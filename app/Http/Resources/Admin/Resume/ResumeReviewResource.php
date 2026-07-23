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
            'position' => $this->position,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'description' => $this->description,
        ];
    }
}

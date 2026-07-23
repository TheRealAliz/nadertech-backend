<?php

namespace App\Http\Resources\Resume;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'image' => asset('storage/' . $this->image),
            'sort_order' => $this->sort_order,
            'alt' => $this->alt,
        ];
    }
}
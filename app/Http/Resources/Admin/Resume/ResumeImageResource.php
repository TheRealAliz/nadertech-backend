<?php

namespace App\Http\Resources\Admin\Resume;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image' => asset('storage/resumes/' . $this->image),
            'sort_order' => $this->sort_order,
            'alt' => $this->alt,
        ];
    }
}
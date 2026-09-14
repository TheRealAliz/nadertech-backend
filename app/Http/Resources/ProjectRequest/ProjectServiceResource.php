<?php

namespace App\Http\Resources\ProjectRequest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_en' => $this->title_en,
            'slug' => $this->slug,
            'description' => $this->description,
            'description_en' => $this->description_en,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,

            'children' => ProjectServiceResource::collection(
                $this->whenLoaded('children')
            )
        ];
    }
}

<?php

namespace App\Http\Resources\Admin\ProjectRequest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_en' => $this->name_en,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'description' => $this->description,
            'description_en' => $this->description_en,
            'type' => $this->requestType,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}

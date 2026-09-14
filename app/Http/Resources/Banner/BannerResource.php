<?php

namespace App\Http\Resources\Banner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_en' => $this->title_en,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'alt' => $this->alt,
            'alt_en' => $this->alt_en,
            'link' => $this->link,
        ];
    }
}

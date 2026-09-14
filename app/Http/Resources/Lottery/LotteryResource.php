<?php

namespace App\Http\Resources\Lottery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotteryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_en' => $this->title_en,
            'description' => $this->description,
            'description_en' => $this->description_en,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'capacity' => $this->capacity,
            'price' => $this->price,
            'winner_count' => $this->winner_count,
            'location' => $this->location,
            'location_en' => $this->location_en,
            'status' => $this->status,
            'drawn_at' => $this->drawn_at,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}

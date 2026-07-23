<?php

namespace App\Http\Resources\Admin\Lottery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotteryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'capacity' => $this->capacity,
            'price' => $this->price,
            'winner_count' => $this->winner_count,
            'status' => $this->status,
            'drawn_at' => $this->drawn_at,
            'entries' => $this->whenLoaded('entries'),
            'winners' => $this->whenLoaded('winners'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
<?php

namespace App\Http\Resources\Lottery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'registered' => (bool) $this->entry,
            'is_winner' => (bool) $this->winner,
            'winner_position' => $this->winner?->position,
            'entry' => EntryResource::make($this->entry),
        ];
    }
}
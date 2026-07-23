<?php

namespace App\Http\Resources\Lottery;

use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => UserResource::make($this->whenLoaded('user')),
            'lottery' => LotteryResource::make($this->whenLoaded('lottery')),
            'registered_at' => $this->registered_at,
            'code' => $this->code,
        ];
    }
}

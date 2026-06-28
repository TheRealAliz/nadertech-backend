<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'username' => $this->username,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'birth_date' => $this->birth_date?->toDateString(),
            'national_code' => $this->national_code,
            'postal_code' => $this->postal_code,
            'province' => $this->province,
            'address' => $this->address,
            'avatar' => $this->avatar ? Storage::url($this->avatar) : null,
            'mobile_verified_at' => $this->mobile_verified_at?->toDateTimeString(),
            'email_verified_at' => $this->email_verified_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
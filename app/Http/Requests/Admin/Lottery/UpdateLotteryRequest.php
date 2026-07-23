<?php

namespace App\Http\Requests\Admin\Lottery;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLotteryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'capacity' => 'nullable|integer|min:1',
            'price' => 'sometimes|required|integer|min:0',
            'winner_count' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|in:draft,active,closed,drawn',
        ];
    }
}
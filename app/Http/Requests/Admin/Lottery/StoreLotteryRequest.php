<?php

namespace App\Http\Requests\Admin\Lottery;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLotteryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'capacity' => 'nullable|integer|min:1',
            'price' => 'required|integer|min:0',
            'winner_count' => 'required|integer|min:1',
            'location' => 'required|string|max:300',
            'status' => 'required|in:draft,active,closed,drawn',
        ];
    }
}

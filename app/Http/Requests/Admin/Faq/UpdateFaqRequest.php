<?php

namespace App\Http\Requests\Admin\Faq;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'question' => 'sometimes|required|string|max:255',
            'answer' => 'sometimes|required|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|nullable|integer',
        ];
    }
}

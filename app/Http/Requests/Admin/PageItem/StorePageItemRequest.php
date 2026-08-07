<?php

namespace App\Http\Requests\Admin\PageItem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePageItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'key' => 'required|string',
            'value' => 'required',
            'type' => 'required|string',
            'page' => 'required|string',
        ];
    }
}

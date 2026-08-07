<?php

namespace App\Http\Requests\Admin\ProjectService;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectServiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|integer|exists:project_services,id',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:project_services,slug',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}

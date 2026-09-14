<?php

namespace App\Http\Requests\Admin\Resume;

use Illuminate\Foundation\Http\FormRequest;

class StoreResumeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:resumes,slug',
            'description' => 'required|string',
            'description_en' => 'nullable|string',
            'is_published' => 'boolean',
            'category_id' => 'nullable|exists:project_services,id',

            'customer_name' => 'string|max:255',
            'customer_name_en' => 'nullable|string|max:255',
            'customer_position' => 'nullable|string|max:255',
            'customer_position_en' => 'nullable|string|max:255',
            'customer_avatar' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'customer_description' => 'string',
            'customer_description_en' => 'nullable|string',

            'images' => 'array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}

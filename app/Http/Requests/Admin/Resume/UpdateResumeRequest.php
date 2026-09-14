<?php

namespace App\Http\Requests\Admin\Resume;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResumeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_en' => 'nullable|string',
            'is_published' => 'boolean',
            'category_id' => 'nullable|exists:project_services,id',

            'customer_name' => 'required|string|max:255',
            'customer_name_en' => 'nullable|string|max:255',
            'customer_position' => 'nullable|string|max:255',
            'customer_position_en' => 'nullable|string|max:255',
            'customer_avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'customer_description' => 'required|string',
            'customer_description_en' => 'nullable|string',

            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'existing_images' => 'sometimes|array',
            'existing_images.*' => 'integer',
            'removed_images' => 'sometimes|array',
            'removed_images.*' => 'integer',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin\ProjectService;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectServiceRequest extends FormRequest
{
    public function rules(): array
    {
        $serviceId = $this->route('service')->id ?? null;

        return [
            'parent_id' => ['nullable', 'integer', 'exists:project_services,id', Rule::notIn([$serviceId])],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('project_services', 'slug')->ignore($serviceId)],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

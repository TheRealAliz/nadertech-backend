<?php

namespace App\Http\Requests\Admin\Resume;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResumeStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_published' => 'required|boolean',
        ];
    }
}
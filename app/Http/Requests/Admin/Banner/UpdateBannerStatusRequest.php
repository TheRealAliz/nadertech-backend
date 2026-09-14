<?php

namespace App\Http\Requests\Admin\Banner;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_active' => 'required|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'is_active.boolean' => 'وضعیت معتبر نیست.'
        ];
    }
}

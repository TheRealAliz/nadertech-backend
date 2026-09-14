<?php

namespace App\Http\Requests\Admin\Banner;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReorderBannerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            '*.id' => 'required|exists:banners,id',
            '*.sort_order' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            '*.id.required' => 'شناسه بنر الزامی است.',
            '*.id.exists' => 'بنر انتخاب شده معتبر نیست.',

            '*.sort_order.required' => 'ترتیب بنر الزامی است.',
            '*.sort_order.integer' => 'ترتیب باید عدد صحیح باشد.',
            '*.sort_order.min' => 'ترتیب نمی‌تواند منفی باشد.',
        ];
    }
}

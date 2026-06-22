<?php

namespace App\Http\Requests\Admin\Banner;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerImageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'image' => 'required|image|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'تصویر بنر الزامی است.',
            'image.image' => 'فایل انتخاب‌شده باید تصویر باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
        ];
    }
}

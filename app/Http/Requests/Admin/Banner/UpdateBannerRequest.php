<?php

namespace App\Http\Requests\Admin\Banner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'alt' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.string' => 'عنوان بنر معتبر نیست.',
            'title.max' => 'عنوان بنر نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

            'image.required' => 'تصویر بنر الزامی است.',
            'image.image' => 'فایل انتخاب‌شده باید تصویر باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',

            'alt.string' => 'متن جایگزین تصویر باید به صورت متن باشد.',
            'alt.max' => 'متن جایگزین نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

            'link.string' => 'لینک معتبر نیست.',
            'link.max' => 'طول لینک نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

            'sort_order.integer' => 'ترتیب نمایش معتبر نیست.',
            'sort_order.min' => 'ترتیب نمایش نمی‌تواند منفی باشد.',

            'is_active.boolean' => 'وضعیت معتبر نیست.',
        ];
    }
}
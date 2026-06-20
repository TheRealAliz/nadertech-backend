<?php

namespace App\Http\Requests\ProjectRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:project_services,id',
            'name' => 'required|string|max:100',
            'mobile' => 'required|string|max:13',
            'email' => 'nullable|email',
            'description' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'service_id.required' => 'انتخاب نوع درخواست الزامی است.',
            'service_id.exists' => 'نوع درخواست انتخاب شده معتبر نیست.',

            'name.required' => 'نام و نام خانوادگی کاربر الزامی می‌باشد.',
            'name.string' => 'نام کاربر معتبر نمی‌باشد.',
            'name.max' => 'نام کاربر نمی‌تواند بیشتر از 150 کاراکتر باشد.',

            'mobile.required' => 'شماره تماس الزامی می‌باشد.',
            'mobile.max' => 'شماره تماس معتبر نمی‌باشد.',

            'email.email' => 'فرمت ایمیل صحیح نمی‌باشد.',

            'description.string' => 'توضیحات معتبر نمی‌باشد.',
        ];
    }
}

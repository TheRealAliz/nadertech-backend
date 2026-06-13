<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOTPRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mobile' => 'required|digits:11|starts_with:09|exists:users,mobile',
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.digits' => 'شماره موبایل باید ۱۱ رقم باشد.',
            'mobile.starts_with' => 'شماره موبایل باید با ۰۹ شروع شود.',
            'mobile.exists' => 'کاربری با این شماره موبایل یافت نشد.',
        ];
    }

    public function attributes(): array
    {
        return [
            'mobile' => 'شماره موبایل',
        ];
    }
}
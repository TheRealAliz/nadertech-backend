<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyForgotPasswordCodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'login' => 'required|string|max:100',
            'code' => 'required|string|size:6',
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'نام کاربری، ایمیل یا شماره موبایل الزامی است.',
            'login.string' => 'فرمت نام کاربری، ایمیل یا شماره موبایل نامعتبر است.',
            'login.max' => 'نام کاربری، ایمیل یا شماره موبایل نباید بیشتر از ۱۰۰ کاراکتر باشد.',
            'code.required' => 'کد تأیید الزامی است.',
            'code.string' => 'فرمت کد تأیید نامعتبر است.',
            'code.size' => 'کد تأیید باید ۶ رقم باشد.',
        ];
    }
}
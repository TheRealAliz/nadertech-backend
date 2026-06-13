<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'login' => 'required|string|max:100',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'نام کاربری، ایمیل یا شماره موبایل الزامی است.',
            'login.string' => 'فرمت نام کاربری، ایمیل یا شماره موبایل نامعتبر است.',
            'login.max' => 'نام کاربری، ایمیل یا شماره موبایل نباید بیشتر از ۱۰۰ کاراکتر باشد.',

            'reset_token.required' => 'توکن بازیابی الزامی است.',
            'reset_token.string' => 'فرمت توکن بازیابی نامعتبر است.',

            'password.required' => 'رمز عبور جدید الزامی است.',
            'password.string' => 'فرمت رمز عبور نامعتبر است.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور مطابقت ندارد.',
        ];
    }
}
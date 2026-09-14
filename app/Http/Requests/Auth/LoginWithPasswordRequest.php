<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginWithPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'login' => 'required|string|max:255',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'نام کاربری، ایمیل یا شماره موبایل الزامی است.',
            'login.string' => 'فرمت نام کاربری، ایمیل یا شماره موبایل نامعتبر است.',
            'login.max' => 'نام کاربری، ایمیل یا شماره موبایل نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.string' => 'فرمت رمز عبور نامعتبر است.',
        ];
    }
}
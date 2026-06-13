<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')
            ],
            'mobile' => [
                'required',
                'digits:11',
                'starts_with:09',
                Rule::unique('users', 'mobile')
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'نام و نام خانوادگی الزامی است.',
            'full_name.string' => 'نام و نام خانوادگی باید متن باشد.',
            'full_name.max' => 'نام و نام خانوادگی نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'username.required' => 'نام کاربری الزامی است.',
            'username.string' => 'نام کاربری باید متن باشد.',
            'username.min' => 'نام کاربری باید حداقل ۳ کاراکتر باشد.',
            'username.max' => 'نام کاربری نباید بیشتر از ۵۰ کاراکتر باشد.',
            'username.alpha_dash' => 'نام کاربری فقط می‌تواند شامل حروف، اعداد، خط تیره و زیرخط باشد.',
            'username.unique' => 'این نام کاربری قبلاً ثبت شده است.',

            'email.required' => 'ایمیل الزامی است.',
            'email.string' => 'ایمیل باید متن باشد.',
            'email.email' => 'فرمت ایمیل نامعتبر است.',
            'email.max' => 'ایمیل نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',

            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.digits' => 'شماره موبایل باید ۱۱ رقم باشد.',
            'mobile.starts_with' => 'شماره موبایل باید با ۰۹ شروع شود.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',

            'password.required' => 'رمز عبور الزامی است.',
            'password.string' => 'رمز عبور باید متن باشد.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور مطابقت ندارد.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => preg_replace('/[^0-9]/', '', $this->mobile),
            'email' => strtolower(trim($this->email)),
            'username' => strtolower(trim($this->username)),
            'full_name' => trim($this->full_name),
        ]);
    }
}
<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'full_name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'username' => [
                'sometimes',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($userId),
            ],

            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'mobile' => [
                'sometimes',
                'digits:11',
                'starts_with:09',
                Rule::unique('users', 'mobile')->ignore($userId),
            ],

            'birth_date' => [
                'sometimes',
                'date',
            ],

            'national_code' => [
                'sometimes',
                'digits:10',
                Rule::unique('users', 'national_code')->ignore($userId),
            ],

            'postal_code' => [
                'sometimes',
                'digits:10',
            ],

            'province' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'address' => [
                'sometimes',
                'string',
            ],

            'avatar' => [
                'sometimes',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'password' => [
                'sometimes',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.max' => 'نام و نام خانوادگی نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'username.unique' => 'این نام کاربری قبلاً ثبت شده است.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'mobile.digits' => 'شماره موبایل باید ۱۱ رقم باشد.',
            'mobile.starts_with' => 'شماره موبایل باید با ۰۹ شروع شود.',
        ];
    }
}

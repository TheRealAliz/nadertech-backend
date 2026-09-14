<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!Hash::check($this->current_password, $this->user()->password)) {
                $validator->errors()->add('current_password', 'رمز عبور فعلی نادرست است.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'رمز عبور فعلی الزامی است.',
            'new_password.required' => 'رمز عبور جدید الزامی است.',
            'new_password.min' => 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.',
            'new_password.confirmed' => 'تکرار رمز عبور جدید مطابقت ندارد.',
        ];
    }
}
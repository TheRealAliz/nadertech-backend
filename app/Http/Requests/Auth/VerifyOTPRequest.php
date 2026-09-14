<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOTPRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'login_token' => 'required|string',
            'code' => 'required|digits:6',
        ];
    }

    public function messages(): array
    {
        return [
            'login_token.required' => 'توکن ورود الزامی است.',
            'login_token.string' => 'فرمت توکن ورود نامعتبر است.',
            'code.required' => 'کد تأیید الزامی است.',
            'code.digits' => 'کد تأیید باید ۶ رقم باشد.',
        ];
    }
}
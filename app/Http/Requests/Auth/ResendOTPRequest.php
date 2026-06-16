<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResendOTPRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'login_token' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'login_token.required' => 'توکن ورودی الزامی است.',
        ];
    }
}
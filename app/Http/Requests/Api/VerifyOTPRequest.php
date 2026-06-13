<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
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
}
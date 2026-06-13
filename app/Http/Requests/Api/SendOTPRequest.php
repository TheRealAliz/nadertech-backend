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
}
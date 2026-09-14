<?php

namespace App\Http\Requests\Lottery;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mobile' => 'required|string',
            'code' => 'required|string',
        ];
    }
}
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => preg_replace('/[^0-9]/', '', $this->mobile),
            'email' => strtolower(trim($this->email)),
            'username' => strtolower(trim($this->username)),
        ]);
    }
}
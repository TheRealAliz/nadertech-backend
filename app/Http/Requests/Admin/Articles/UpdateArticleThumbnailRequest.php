<?php

namespace App\Http\Requests\Admin\Articles;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleThumbnailRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'thumbnail' => 'required|image|max:2048'
        ];
    }
}

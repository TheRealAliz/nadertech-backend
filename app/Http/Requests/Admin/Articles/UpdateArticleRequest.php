<?php

namespace App\Http\Requests\Admin\Articles;

use App\Enums\ArticleStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('articles', 'slug')->ignore($this->article->id),
            ],
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
            'thumbnail_alt' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'status' => ['required', Rule::enum(ArticleStatus::class)],
        ];
    }
}

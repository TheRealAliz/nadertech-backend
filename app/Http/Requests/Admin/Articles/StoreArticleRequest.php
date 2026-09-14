<?php

namespace App\Http\Requests\Admin\Articles;

use App\Enums\ArticleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:500',
            'slug' => 'required|string|max:255|unique:articles,slug',
            'content' => 'required|string',
            'content_en' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'thumbnail_alt' => 'nullable|string|max:255',
            'thumbnail_alt_en' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_title_en' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_description_en' => 'nullable|string',
            'status' => ['required', Rule::enum(ArticleStatus::class)],
        ];
    }
}

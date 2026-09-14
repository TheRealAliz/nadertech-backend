<?php

namespace App\Http\Resources\Faq;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'question_en' => $this->question_en,
            'answer' => $this->answer,
            'answer_en' => $this->answer_en,
            'sort_order' => $this->sort_order,
        ];
    }
}

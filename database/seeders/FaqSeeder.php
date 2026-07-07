<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'فرآیند کار از ابتدا تا انتها به چه صورت است؟',
                'answer' => 'فرآیند کار ما از ابتدا تا انتها در ۶ مرحله‌ی شفاف و منظم انجام می‌شود تا شما در هر لحظه از پیشرفت پروژه خود مطلع باشید.',
                'is_active' => true,
            ],
            [
                'question' => 'تولید محتوا فقط متنی است یا تصویر و ویدیو هم شامل می‌شود؟',
                'answer' => 'خیر، خدمات ما شامل تولید محتوای متنی، تصویری، ویدئویی و گرافیک‌های اختصاصی می‌باشد.',
                'is_active' => true,
            ],
            [
                'question' => 'سایت من با چه سیستمی طراحی می‌شود؟',
                'answer' => 'بسته به نیاز و اهداف شما، ما از بهترین تکنولوژی‌ها استفاده می‌کنیم.',
                'is_active' => true,
            ],
            [
                'question' => 'طراحی سایت و تولید محتوا را با هم انجام می‌دهید یا جداگانه؟',
                'answer' => 'هر دو سرویس هم به صورت پکیج جامع و هم به صورت جداگانه قابل ارائه هستند.',
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $key => $value) {
            Faq::updateOrCreate([
                'question' => $value['question'],
            ], [
                'answer' => $value['answer'],
                'is_active' => true,
                'sort_order' => $key + 1,
            ]);
        }
    }
}

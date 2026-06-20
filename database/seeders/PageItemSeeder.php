<?php

namespace Database\Seeders;

use App\Enums\AboutUsContentType;
use App\Models\PageItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageItemSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'key' => AboutUsContentType::TITLE->value,
                'value' => 'نادر تکنولوژی فقط یک نام نیست؛ یک نگاه است.',
                'page' => 'about',
                'type' => 'text'
            ],
            [
                'key' => AboutUsContentType::INTRO_TEXT->value,
                'value' => 'نگاهی که از جسارت، دقت و ساختن مسیرهای تازه الهام گرفته شده است. در دنیایی که تکنولوژی هر روز در حال تغییر است، ما باور داریم موفقیت فقط در همراه شدن با این تغییرات نیست؛ بلکه در خلق مسیرهای جدید و ساختن راه‌حل‌هایی است که بتوانند رشد واقعی ایجاد کنند.',
                'page' => 'about',
                'type' => 'text'
            ],
            [
                'key' => AboutUsContentType::DESCRIPTION_TOP->value,
                'value' => 'نادر تکنولوژی مجموعه‌ای تخصصی در حوزه تکنولوژی، توسعه وب، تولید محتوا و توسعه برند است که با ترکیب خلاقیت، استراتژی و فناوری، ایده‌ها را به پروژه‌هایی قابل رشد و ماندگار تبدیل می‌کند. ما معتقدیم هر پروژه فراتر از یک سفارش است؛ فرصتی برای ساختن تجربه‌ای ارزشمند که بتواند برای یک کسب‌وکار مسیر تازه‌ای ایجاد کند.',
                'page' => 'about',
                'type' => 'text'
            ]
            ,
            [
                'key' => AboutUsContentType::DESCRIPTION_BOTTOM->value,
                'value' => 'تیم ما با بیش از ۱۵ سال تجربه حرفه‌ای، پروژه‌ها را از مرحله ایده تا اجرا و توسعه همراهی می‌کند. از طراحی و توسعه وب‌سایت‌های اختصاصی، فروشگاه‌های اینترنتی و سیستم‌های تحت وب گرفته تا تولید محتوای حرفه‌ای، هویت بصری و راهکارهای رسانه‌ای؛ تمرکز ما تنها روی اجرا نیست، بلکه روی ساخت درست است؛ جایی که عملکرد، تجربه کاربری، امنیت و رشد بلندمدت در کنار یکدیگر معنا پیدا می‌کنند.',
                'page' => 'about',
                'type' => 'text'
            ],
            [
                'key' => AboutUsContentType::BANNER_IMAGE->value,
                'value' => asset('images/about/banner_image.jpg'),
                'page' => 'about',
                'type' => 'image_path'
            ]
        ];

        foreach ($data as $value) {
            PageItem::updateOrCreate([
                'key' => $value['key'],
                'page' => $value['page'],
                'type' => $value['type']
            ], [
                'value' => $value['value']
            ]);
        }
    }
}

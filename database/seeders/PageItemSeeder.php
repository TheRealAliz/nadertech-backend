<?php

namespace Database\Seeders;

use App\Enums\AboutUsContentType;
use App\Models\PageItem;
use Illuminate\Database\Seeder;

class PageItemSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'key' => AboutUsContentType::Title->value,
                'value' => 'نادر تکنولوژی فقط یک نام نیست؛ یک نگاه است.',
                'page' => 'about',
                'type' => 'text'
            ],
            [
                'key' => AboutUsContentType::IntroText->value,
                'value' => 'نگاهی که از جسارت، دقت و ساختن مسیرهای تازه الهام گرفته شده است. در دنیایی که تکنولوژی هر روز در حال تغییر است، ما باور داریم موفقیت فقط در همراه شدن با این تغییرات نیست؛ بلکه در خلق مسیرهای جدید و ساختن راه‌حل‌هایی است که بتوانند رشد واقعی ایجاد کنند.',
                'page' => 'about',
                'type' => 'text'
            ],
            [
                'key' => AboutUsContentType::DescriptionTop->value,
                'value' => 'نادر تکنولوژی مجموعه‌ای تخصصی در حوزه تکنولوژی، توسعه وب، تولید محتوا و توسعه برند است که با ترکیب خلاقیت، استراتژی و فناوری، ایده‌ها را به پروژه‌هایی قابل رشد و ماندگار تبدیل می‌کند. ما معتقدیم هر پروژه فراتر از یک سفارش است؛ فرصتی برای ساختن تجربه‌ای ارزشمند که بتواند برای یک کسب‌وکار مسیر تازه‌ای ایجاد کند.',
                'page' => 'about',
                'type' => 'text'
            ]
            ,
            [
                'key' => AboutUsContentType::DescriptionBottom->value,
                'value' => 'تیم ما با بیش از ۱۵ سال تجربه حرفه‌ای، پروژه‌ها را از مرحله ایده تا اجرا و توسعه همراهی می‌کند. از طراحی و توسعه وب‌سایت‌های اختصاصی، فروشگاه‌های اینترنتی و سیستم‌های تحت وب گرفته تا تولید محتوای حرفه‌ای، هویت بصری و راهکارهای رسانه‌ای؛ تمرکز ما تنها روی اجرا نیست، بلکه روی ساخت درست است؛ جایی که عملکرد، تجربه کاربری، امنیت و رشد بلندمدت در کنار یکدیگر معنا پیدا می‌کنند.',
                'page' => 'about',
                'type' => 'text'
            ],
            [
                'key' => AboutUsContentType::BannerImage->value,
                'value' => asset('images/about/banner_image.jpg'),
                'page' => 'about',
                'type' => 'image_path'
            ],
            [
                'key' => 'description_1',
                'value' => ' ما با ارائه خدمات تخصصی در برنامه‌ریزی، مدیریت و اجرای رویدادها، از ایده‌پردازی تا برگزاری نهایی در کنار شما هستیم.',
                'page' => 'events',
                'type' => 'text'
            ],
            [
                'key' => 'description_2',
                'value' => 'برگزاری انواع همایش‌ها، سمینارها، کنفرانس‌ها، بوت‌کمپ‌ها و کارگاه‌های آموزشی در حوزه‌های فناوری، برنامه‌نویسی، هوش مصنوعی، تولید محتوا، رسانه‌های دیجیتال و کارآفرینی، همراه با خدمات اجرایی، آموزشی، رسانه‌ای، فنی، تبلیغاتی، شبکه‌سازی حرفه‌ای و جذب سرمایه‌گذار، بخشی از راهکارهای ما برای خلق رویدادهای اثرگذار و ماندگار است.',
                'page' => 'events',
                'type' => 'text'
            ],
            [
                'key' => 'service_1_title',
                'value' => 'مدیریت و اجرای رویداد',
                'page' => 'events',
                'type' => 'text'
            ],
            [
                'key' => 'service_2_title',
                'value' => 'رویدادهای آموزشی و تخصصی',
                'page' => 'events',
                'type' => 'text'
            ],
            [
                'key' => 'service_3_title',
                'value' => 'رسانه، شبکه‌سازی و توسعه کسب‌وکار',
                'page' => 'events',
                'type' => 'text'
            ],
            [
                'key' => 'service_1_description',
                'value' => 'از برنامه‌ریزی و طراحی سناریو تا هماهنگی تیم اجرایی، ثبت‌نام شرکت‌کنندگان و مدیریت کامل فرآیند برگزاری رویداد.',
                'page' => 'events',
                'type' => 'text'
            ],
            [
                'key' => 'service_2_description',
                'value' => 'برگزاری بوت‌کمپ‌ها، سمینارها و رویدادهای تخصصی در حوزه فناوری، برنامه‌نویسی، هوش مصنوعی، تولید محتوا و کارآفرینی.',
                'page' => 'events',
                'type' => 'text'
            ],
            [
                'key' => 'service_3_description',
                'value' => 'ارائه خدمات رسانه‌ای و فنی، جذب حامی مالی، برگزاری جلسات B2B، شبکه‌سازی حرفه‌ای و ایجاد فرصت‌های همکاری و رشد کسب‌وکار.',
                'page' => 'events',
                'type' => 'text'
            ],
            [
                'key' => 'event_location',
                'value' => 'یه جایی',
                'page' => 'events',
                'type' => 'text'
            ],
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

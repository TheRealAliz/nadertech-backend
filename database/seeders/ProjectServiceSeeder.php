<?php

namespace Database\Seeders;

use App\Models\ProjectService;
use Illuminate\Database\Seeder;

class ProjectServiceSeeder extends Seeder
{
    public function run(): void
    {
        $webDesign = ProjectService::firstOrCreate(
            ['slug' => 'web-design'],
            [
                'title' => 'طراحی سایت',
                'description' => 'طراحی و توسعه انواع وب‌سایت‌ها و سیستم‌های تحت وب',
                'sort_order' => 1,
                'is_active' => true,
                'parent_id' => null,
            ]
        );

        $webDesignChildren = [
            [
                'slug' => 'ui-ux-design',
                'title' => 'طراحی UI/UX مدرن و کاربرمحور',
                'description' => 'طراحی رابط کاربری و تجربه کاربری مدرن با رویکرد کاربرمحور',
                'sort_order' => 1,
            ],
            [
                'slug' => 'performance-optimization',
                'title' => 'بهینه‌سازی عملکرد و سرعت پروژه‌ها',
                'description' => 'بهینه‌سازی عملکرد، سرعت بارگذاری و کارایی پروژه‌های تحت وب',
                'sort_order' => 2,
            ],
            [
                'slug' => 'backend-development',
                'title' => 'توسعه فرآیند و بک‌اند',
                'description' => 'توسعه سمت سرور، منطق کسب‌وکار و پردازش داده‌ها',
                'sort_order' => 3,
            ],
            [
                'slug' => 'technical-consulting',
                'title' => 'مشاوره فنی و تحلیل ایده‌های کسب‌وکار',
                'description' => 'مشاوره فنی، تحلیل و بررسی ایده‌های کسب‌وکار برای پیاده‌سازی دیجیتال',
                'sort_order' => 4,
            ],
            [
                'slug' => 'admin-panels-and-apis',
                'title' => 'طراحی پنل‌های مدیریتی و API',
                'description' => 'طراحی و توسعه پنل‌های مدیریتی پیشرفته و APIهای قدرتمند',
                'sort_order' => 5,
            ],
            [
                'slug' => 'ecommerce-and-web-systems',
                'title' => 'طراحی فروشگاه‌های اینترنتی و سیستم‌های تحت وب',
                'description' => 'طراحی و توسعه فروشگاه‌های اینترنتی و سیستم‌های جامع تحت وب',
                'sort_order' => 6,
            ],
        ];

        foreach ($webDesignChildren as $service) {
            ProjectService::updateOrCreate(
                ['slug' => $service['slug']],
                [
                    'title' => $service['title'],
                    'description' => $service['description'],
                    'sort_order' => $service['sort_order'],
                    'is_active' => true,
                    'parent_id' => $webDesign->id,
                ]
            );
        }

        $contentProduction = ProjectService::firstOrCreate(
            ['slug' => 'content-production'],
            [
                'title' => 'تولید محتوا',
                'description' => 'تولید محتوای حرفه‌ای و جذاب برای شبکه‌های اجتماعی و برندینگ',
                'sort_order' => 2,
                'is_active' => true,
                'parent_id' => null,
            ]
        );

        $contentChildren = [
            [
                'slug' => 'filming-and-editing',
                'title' => 'فیلم‌برداری و تدوین',
                'description' => 'فیلم‌برداری حرفه‌ای و تدوین ویدئوهای تبلیغاتی و محتوایی',
                'sort_order' => 1,
            ],
            [
                'slug' => 'social-media-videos',
                'title' => 'تولید ویدئوهای حرفه‌ای شبکه‌های اجتماعی',
                'description' => 'تولید ویدئوهای جذاب و ویروسی برای شبکه‌های اجتماعی',
                'sort_order' => 2,
            ],
            [
                'slug' => 'motion-graphics',
                'title' => 'موشن گرافیک و هویت بصری',
                'description' => 'طراحی موشن گرافیک و هویت بصری برای برندها و کمپین‌ها',
                'sort_order' => 3,
            ],
            [
                'slug' => 'reels-production',
                'title' => 'ساخت ریلزهای ویدئومحور',
                'description' => 'ساخت ریلزهای جذاب و ویدئومحور برای اینستاگرام و سایر شبکه‌ها',
                'sort_order' => 4,
            ],
            [
                'slug' => 'brand-content-campaigns',
                'title' => 'طراحی کمپین‌های محتوایی برند',
                'description' => 'طراحی و اجرای کمپین‌های محتوایی جامع برای برندها',
                'sort_order' => 5,
            ],
            [
                'slug' => 'scriptwriting',
                'title' => 'سناریو نویسی و ایده‌پردازی',
                'description' => 'سناریو نویسی حرفه‌ای و ایده‌پردازی برای تولید محتوای خلاقانه',
                'sort_order' => 6,
            ],
        ];

        foreach ($contentChildren as $service) {
            ProjectService::updateOrCreate(
                ['slug' => $service['slug']],
                [
                    'title' => $service['title'],
                    'description' => $service['description'],
                    'sort_order' => $service['sort_order'],
                    'is_active' => true,
                    'parent_id' => $contentProduction->id,
                ]
            );
        }

        $eventManagement = ProjectService::firstOrCreate(
            ['slug' => 'event-management'],
            [
                'title' => 'برگزاری ایونت',
                'description' => 'برگزاری رویدادهای حرفه‌ای، شبکه‌سازی و توسعه اکوسیستم کسب‌وکار',
                'sort_order' => 3,
                'is_active' => true,
                'parent_id' => null,
            ]
        );

        $eventChildren = [
            [
                'slug' => 'digital-marketing-consulting',
                'title' => 'مشاوره دیجیتال مارکتینگ',
                'description' => 'ارائه مشاوره تخصصی در حوزه دیجیتال مارکتینگ و استراتژی‌های آنلاین',
                'sort_order' => 1,
            ],
            [
                'slug' => 'event-planning',
                'title' => 'برگزاری ایونت‌ها و رویدادها',
                'description' => 'برنامه‌ریزی و اجرای حرفه‌ای ایونت‌ها و رویدادهای کسب‌وکاری',
                'sort_order' => 2,
            ],
            [
                'slug' => 'team-communication',
                'title' => 'ایجاد ارتباط بین تیم‌ها و متخصصان',
                'description' => 'ایجاد بسترهای ارتباطی و همکاری بین تیم‌ها و متخصصان صنایع مختلف',
                'sort_order' => 3,
            ],
            [
                'slug' => 'brand-networking',
                'title' => 'شبکه‌سازی بین برندها و کسب‌وکار',
                'description' => 'ایجاد شبکه‌های همکاری و ارتباط بین برندها و کسب‌وکارهای مختلف',
                'sort_order' => 4,
            ],
            [
                'slug' => 'business-ecosystem',
                'title' => 'توسعه اکوسیستم کسب‌وکارها',
                'description' => 'توسعه و تقویت اکوسیستم کسب‌وکارها از طریق همکاری‌های بین‌بخشی',
                'sort_order' => 5,
            ],
            [
                'slug' => 'brand-growth',
                'title' => 'مشاوره رشد برند و توسعه کسب‌وکار',
                'description' => 'مشاوره در زمینه رشد برند و توسعه استراتژیک کسب‌وکارها',
                'sort_order' => 6,
            ],
        ];

        foreach ($eventChildren as $service) {
            ProjectService::updateOrCreate(
                ['slug' => $service['slug']],
                [
                    'title' => $service['title'],
                    'description' => $service['description'],
                    'sort_order' => $service['sort_order'],
                    'is_active' => true,
                    'parent_id' => $eventManagement->id,
                ]
            );
        }
    }
}
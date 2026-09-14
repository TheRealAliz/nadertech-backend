<?php

namespace Database\Seeders;

use App\Models\ProjectRequestType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectRequestTypeSeeder extends Seeder
{
    public function run(): void
    {
        ProjectRequestType::whereIn('id', [6, 7, 8, 9, 10])->delete();
        $types = [
            [
                'title' => 'پروژه طراحی سایت',
                'title_en' => 'Website Design Project'
            ],
            [
                'title' => 'پروژه تولید محتوا',
                'title_en' => 'Content Production Project'
            ],
            [
                'title' => 'برگذاری ایونت',
                'title_en' => 'Event Hosting'
            ],
            [
                'title' => 'درخواست همکاری',
                'title_en' => 'Collaboration Request'
            ],
            [
                'title' => 'انتقادات و پیشنهادات',
                'title_en' => 'Criticisms and Suggestions'
            ],
        ];

        DB::transaction(function () use ($types) {
            foreach ($types as $type) {
                ProjectRequestType::updateOrCreate(['title' => $type['title']], $type);
            }
        });
    }
}

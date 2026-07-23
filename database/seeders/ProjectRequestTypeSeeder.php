<?php

namespace Database\Seeders;

use App\Models\ProjectRequestType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectRequestTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'title' => 'پروژه طراحی سایت',
            ],
            [
                'title' => 'پروژه تولید محتوا',
            ],
            [
                'title' => 'برگذاری ایونت',
            ],
            [
                'title' => 'درخواست همکاری',
            ],
            [
                'title' => 'انتقادات و پیشنهادات',
            ],
        ];

        DB::transaction(function () use ($types) {
            foreach ($types as $type) {
                ProjectRequestType::updateOrCreate($type, $type);
            }
        });
    }
}

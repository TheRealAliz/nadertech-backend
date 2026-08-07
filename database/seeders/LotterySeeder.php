<?php

namespace Database\Seeders;

use App\Models\Lottery;
use Illuminate\Database\Seeder;

class LotterySeeder extends Seeder
{
    public function run(): void
    {
        Lottery::create([
            'title' => 'اولین قرعه‌کشی',
            'description' => 'خوش آمدید به قرعه‌کشی ما.',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'capacity' => 20,
            'price' => 10000,
            'winner_count' => 5,
            'location' => 'بجنورد، دفتر مرکزی',
            'status' => 'active',
        ]);
    }
}

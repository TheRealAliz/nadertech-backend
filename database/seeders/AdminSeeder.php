<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            [
                'username' => 'superadmin',
            ],
            [
                'full_name' => 'Super Admin',
                'mobile' => '09123456789',
                'password' => Hash::make('admin'),
                'is_active' => true,
            ]
        );

        Admin::updateOrCreate(
            [
                'username' => 'mainAdmin',
            ],
            [
                'full_name' => 'Main Admin',
                'mobile' => '09987654321',
                'password' => Hash::make('main123456'),
                'is_active' => true,
            ]
        );
    }
}

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
        $admin = Admin::updateOrCreate(
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
    }
}

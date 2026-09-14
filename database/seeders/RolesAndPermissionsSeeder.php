<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [

            // Users
            'admin.users.view',
            'admin.users.view.single',

            // Admins
            'admin.admins.roles.view',
            'admin.admins.roles.update',
            'admin.admins.roles.permissions.view',
            'admin.admins.roles.permissions.update',

            // Banners
            'admin.banners.view',
            'admin.banners.create',
            'admin.banners.update',
            'admin.banners.delete',
            'admin.banners.update.status',
            'admin.banners.update.image',
            'admin.banners.reorder',

            // Articles
            'admin.articles.view',
            'admin.articles.create',
            'admin.articles.update',
            'admin.articles.delete',
            'admin.articles.archived.view',
            'admin.articles.update.status',
            'admin.articles.update.thumbnail',

            // Lotteries
            'admin.lotteries.view',
            'admin.lotteries.create',
            'admin.lotteries.update',
            'admin.lotteries.delete',
            'admin.lotteries.draw',
            'admin.lotteries.entries.view',
            'admin.lotteries.winners.view',

            // Project Requests
            'admin.requests.view',

            // Resumes
            'admin.resumes.view',
            'admin.resumes.create',
            'admin.resumes.update',
            'admin.resumes.delete',

            // FAQs
            'admin.faqs.view',
            'admin.faqs.create',
            'admin.faqs.update',
            'admin.faqs.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'admin',
        ]);

        $viewer = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'admin',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Assign Permissions
        |--------------------------------------------------------------------------
        */

        // Super Admin → همه دسترسی‌ها
        $superAdmin->syncPermissions(
            Permission::where('guard_name', 'admin')->get()
        );

        // Viewer → فقط دسترسی‌های مشاهده
        $viewer->syncPermissions([
            'admin.users.view',
            'admin.users.view.single',

            'admin.admins.roles.view',
            'admin.admins.roles.permissions.view',

            'admin.banners.view',

            'admin.articles.view',
            'admin.articles.archived.view',

            'admin.lotteries.view',
            'admin.lotteries.entries.view',
            'admin.lotteries.winners.view',

            'admin.requests.view',

            'admin.resumes.view',

            'admin.faqs.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Assign Roles
        |--------------------------------------------------------------------------
        */

        Admin::where('username', 'superadmin')->first()?->assignRole('super-admin');
        Admin::where('username', 'mainAdmin')->first()?->assignRole('viewer');
    }
}

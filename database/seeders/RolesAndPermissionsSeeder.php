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

            // Banners
            'admin.banners.show',
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
            'admin.lotteries.create',
            'admin.lotteries.update',
            'admin.lotteries.delete',
            'admin.lotteries.entries.view',
            'admin.lotteries.draw',
            'admin.lotteries.winners.view',

            // Project Requests
            'admin.requests.view'
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

        /*
        |--------------------------------------------------------------------------
        | Assign Permissions
        |--------------------------------------------------------------------------
        */

        $superAdmin->syncPermissions(Permission::all());

        Admin::find(1)->assignRole('super-admin');
    }
}
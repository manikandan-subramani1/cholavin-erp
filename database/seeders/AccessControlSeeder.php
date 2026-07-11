<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'products' => ['view', 'create', 'update', 'delete'],
            'enquiries' => ['view', 'update'],
            'users' => ['view', 'create', 'update', 'delete'],
            'roles' => ['view', 'create', 'update', 'delete'],
            'shops' => ['view', 'create', 'update', 'delete'],
            'godowns' => ['view', 'create', 'update', 'delete'],
            'activity-logs' => ['view'],
            'settings' => ['view', 'update'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::updateOrCreate(
                    ['code' => "{$module}.{$action}"],
                    ['module' => $module, 'action' => $action, 'label' => ucfirst($action).' '.str($module)->replace('-', ' ')->title()]
                );
            }
        }

        $role = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'is_super_admin' => true, 'is_active' => true]
        );

        $role->permissions()->sync(Permission::pluck('id'));

        $admin = User::firstOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'admin@cholavin.test')],
            [
                'name' => 'Super Admin',
                'username' => env('SUPER_ADMIN_USERNAME', 'superadmin'),
                'mobile' => env('SUPER_ADMIN_MOBILE'),
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'ChangeMe@123')),
            ]
        );

        $admin->forceFill(['role_id' => $role->id, 'is_active' => true])->save();
    }
}

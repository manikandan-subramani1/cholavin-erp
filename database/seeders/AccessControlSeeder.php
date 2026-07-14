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
        $modules = config('erp_permissions');

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

        $username = env('SUPER_ADMIN_USERNAME', 'superadmin');
        $admin = User::query()->where('username', $username)->first()
            ?? User::query()->where('role_id', $role->id)->oldest('id')->first()
            ?? new User;

        $admin->forceFill([
            'name' => 'Super Admin',
            'username' => $username,
            'email' => env('SUPER_ADMIN_EMAIL', 'admin@gmail.com'),
            'mobile' => env('SUPER_ADMIN_MOBILE'),
            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', '12345678')),
            'role_id' => $role->id,
            'is_active' => true,
        ])->save();
    }
}

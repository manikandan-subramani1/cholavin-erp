<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissionId = DB::table('permissions')->insertGetId([
            'module' => 'sales',
            'action' => 'view',
            'code' => 'sales.view',
            'label' => 'View Sales',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (DB::table('roles')->where('is_super_admin', true)->pluck('id') as $roleId) {
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('code', 'sales.view')->value('id');
        if ($permissionId) {
            DB::table('permission_role')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};

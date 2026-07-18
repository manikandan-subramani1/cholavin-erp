<?php

namespace App\Services\Access;

use App\Models\Godown;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccessControlService
{
    public function createUser(array $data, int $actorId): User
    {
        return DB::transaction(function () use ($data, $actorId): User {
            [$attributes, $shops, $godowns, $financialYears] = $this->extractUserAssignments($data);
            $user = User::create($attributes);
            $this->syncAssignments($user, $shops, $godowns, $financialYears, $actorId);

            return $user->load(['role:id,name', 'shops:id,name', 'godowns:id,name', 'financialYears:id,name']);
        });
    }

    public function updateUser(User $user, array $data, int $actorId): User
    {
        return DB::transaction(function () use ($user, $data, $actorId): User {
            [$attributes, $shops, $godowns, $financialYears] = $this->extractUserAssignments($data);
            if (empty($attributes['password'])) {
                unset($attributes['password']);
            }
            $user->update($attributes);
            $this->syncAssignments($user, $shops, $godowns, $financialYears, $actorId);

            return $user->load(['role:id,name', 'shops:id,name', 'godowns:id,name', 'financialYears:id,name']);
        });
    }

    public function syncUserPermissions(User $user, array $overrides): User
    {
        return DB::transaction(function () use ($user, $overrides): User {
            $sync = collect($overrides)
                ->reject(fn (string $value) => $value === 'inherit')
                ->mapWithKeys(fn (string $value, string $permissionId) => [
                    (int) $permissionId => ['allowed' => $value === 'allow'],
                ])->all();
            $user->permissions()->sync($sync);

            return $user->load(['role.permissions', 'permissions']);
        });
    }

    public function createRole(array $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            $role = Role::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'is_active' => true,
            ]);
            $role->permissions()->sync($data['permissions'] ?? []);

            return $role->load('permissions');
        });
    }

    public function updateRole(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data): Role {
            $role->update([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'is_active' => $data['is_active'],
            ]);
            if (! $role->is_super_admin) {
                $role->permissions()->sync($data['permissions'] ?? []);
            }

            return $role->load('permissions');
        });
    }

    public function createShop(array $data): Shop
    {
        return Shop::create($data + ['is_active' => true]);
    }

    public function updateShop(Shop $shop, array $data): Shop
    {
        $shop->update($data);

        return $shop->refresh();
    }

    public function createGodown(array $data): Godown
    {
        return DB::transaction(function () use ($data): Godown {
            $shopIds = collect($data['shop_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
            unset($data['shop_ids']);
            $godown = Godown::create($data + ['shop_id' => $shopIds->first(), 'is_active' => true]);
            $godown->shops()->sync($shopIds);

            return $godown->load('shops:id,name');
        });
    }

    public function updateGodown(Godown $godown, array $data): Godown
    {
        return DB::transaction(function () use ($godown, $data): Godown {
            $shopIds = collect($data['shop_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
            unset($data['shop_ids']);
            $godown->update($data + ['shop_id' => $shopIds->first()]);
            $godown->shops()->sync($shopIds);

            return $godown->load('shops:id,name');
        });
    }

    private function extractUserAssignments(array $data): array
    {
        $shops = collect($data['shops'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $godowns = collect($data['godowns'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $financialYears = collect($data['financial_years'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        unset($data['shops'], $data['godowns'], $data['financial_years']);

        return [$data, $shops, $godowns, $financialYears];
    }

    private function syncAssignments(User $user, $shopIds, $godownIds, $financialYearIds, int $actorId): void
    {
        $user->shops()->sync($shopIds->mapWithKeys(fn (int $id, int $index) => [
            $id => ['is_default' => $index === 0, 'is_active' => true, 'created_by' => $actorId],
        ])->all());
        $user->godowns()->sync($godownIds->mapWithKeys(fn (int $id, int $index) => [
            $id => ['is_default' => $index === 0, 'is_active' => true, 'created_by' => $actorId],
        ])->all());
        $user->financialYears()->sync($financialYearIds->mapWithKeys(fn (int $id, int $index) => [
            $id => ['is_default' => $index === 0, 'is_active' => true, 'created_by' => $actorId],
        ])->all());
    }
}

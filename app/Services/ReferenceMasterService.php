<?php

namespace App\Services;

use App\Models\ReferenceMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReferenceMasterService
{
    public function filteredQuery(string $type, Request $request): Builder
    {
        $search = is_array($request->input('search'))
            ? data_get($request->input('search'), 'value')
            : $request->input('search');

        return ReferenceMaster::query()
            ->with('parent:id,name')
            ->ofType($type)
            ->forCurrentShop()
            ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->when($request->filled('parent_id'), fn ($query) => $query->where('parent_id', $request->integer('parent_id')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to_date')));
    }

    public function create(array $data, array $module): ReferenceMaster
    {
        return ReferenceMaster::create($this->payload($data, $module));
    }

    public function update(ReferenceMaster $master, array $data, array $module): ReferenceMaster
    {
        $master->update($this->payload($data, $module));

        return $master->refresh();
    }

    private function payload(array $data, array $module): array
    {
        return [
            'type' => $module['type'],
            'parent_id' => $data['parent_id'] ?? null,
            'shop_id' => ($module['shop_scoped'] ?? false) ? session('active_shop_id') : null,
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'description' => $data['description'] ?? null,
            'percentage' => $data['percentage'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'is_active' => (bool) $data['is_active'],
        ];
    }
}

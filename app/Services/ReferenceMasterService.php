<?php

namespace App\Services;

use App\Models\ReferenceMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferenceMasterService
{
    public function filteredQuery(string $type, Request $request): Builder
    {
        $search = is_array($request->input('search'))
            ? data_get($request->input('search'), 'value')
            : $request->input('search');

        return ReferenceMaster::query()
            ->with('parent:id,name')
            ->withExists('children')
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

    public function findForModule(string $type, int|string $id): ReferenceMaster
    {
        return ReferenceMaster::query()
            ->ofType($type)
            ->forCurrentShop()
            ->findOrFail($id);
    }

    public function createForModule(string $type, array $data, array $coreFields, array $fields, bool $shopScoped): ReferenceMaster
    {
        return ReferenceMaster::create($this->modulePayload($type, $data, $coreFields, $fields, $shopScoped));
    }

    public function updateForModule(ReferenceMaster $record, array $data, array $coreFields, array $fields, bool $shopScoped): ReferenceMaster
    {
        $record->update($this->modulePayload($record->type, $data, $coreFields, $fields, $shopScoped, $record));

        return $record->refresh();
    }

    public function formValues(ReferenceMaster $record, array $coreFields, array $fields): array
    {
        return collect($fields)->mapWithKeys(fn (string $field) => [
            $field => $this->fieldValue($record, $field, $coreFields),
        ])->all();
    }

    public function fieldValue(ReferenceMaster $record, string $field, array $coreFields): mixed
    {
        $coreColumn = array_search($field, $coreFields, true);

        if ($coreColumn !== false) {
            return match ($coreColumn) {
                'status' => data_get($record->metadata, 'status', $record->is_active ? 'Active' : 'Inactive'),
                default => $record->{$coreColumn},
            };
        }

        return data_get($record->metadata, $field)
            ?? ($field === 'ifsc_code' ? data_get($record->metadata, 'ifsc') : null);
    }

    private function modulePayload(string $type, array $data, array $coreFields, array $fields, bool $shopScoped, ?ReferenceMaster $record = null): array
    {
        $nameField = $coreFields['name'];
        $codeField = $coreFields['code'] ?? null;
        $descriptionField = $coreFields['description'] ?? null;
        $percentageField = $coreFields['percentage'] ?? null;
        $statusField = $coreFields['status'] ?? 'status';
        $status = (string) ($data[$statusField] ?? 'Active');
        $baseCode = $codeField ? (string) ($data[$codeField] ?? '') : (string) ($data[$nameField] ?? 'record');
        $code = $record && ! $codeField ? $record->code : $this->uniqueCode($type, $baseCode, $record?->id);
        $metadata = collect($fields)
            ->reject(fn (string $field) => in_array($field, array_filter($coreFields), true))
            ->mapWithKeys(fn (string $field) => [$field => $data[$field] ?? null])
            ->all();
        $metadata['status'] = $status;

        return [
            'type' => $type,
            'parent_id' => null,
            'shop_id' => $shopScoped ? session('active_shop_id') : null,
            'name' => $data[$nameField],
            'code' => $code,
            'description' => $descriptionField ? ($data[$descriptionField] ?? null) : null,
            'percentage' => $percentageField ? $this->numericPercentage($data[$percentageField] ?? null) : null,
            'metadata' => $metadata,
            'is_active' => Str::lower($status) === 'active',
        ];
    }

    private function uniqueCode(string $type, string $value, ?int $ignoreId = null): string
    {
        $base = Str::upper(Str::slug($value, '-')) ?: 'RECORD';
        $candidate = $base;
        $suffix = 2;

        while (ReferenceMaster::query()->ofType($type)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->where('code', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function numericPercentage(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) preg_replace('/[^0-9.\-]/', '', (string) $value);
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

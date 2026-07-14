<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DataMaintenanceService
{
    private const BACKUP_TABLES = [
        'settings', 'roles', 'permissions', 'users', 'shops', 'godowns', 'reference_masters', 'products',
        'parties', 'party_addresses', 'commercial_documents', 'commercial_document_items', 'inventory_balances',
        'inventory_movements', 'stock_transfers', 'stock_transfer_items', 'payments', 'ledger_accounts',
        'vouchers', 'voucher_lines', 'deliveries', 'user_notifications',
    ];

    public function backups(): array
    {
        $directory = $this->backupDirectory();
        return collect(File::files($directory))->map(fn (\SplFileInfo $file) => [
            'name' => $file->getFilename(),
            'size' => round($file->getSize() / 1024, 2).' KB',
            'created_at' => date('d-m-Y h:i A', $file->getMTime()),
        ])->sortByDesc('name')->values()->all();
    }

    public function createBackup(): array
    {
        $name = 'cholavin-erp-'.now()->format('Ymd-His').'.ndjson.gz';
        $path = $this->backupDirectory().DIRECTORY_SEPARATOR.$name;
        $stream = gzopen($path, 'wb9');
        if (! $stream) {
            throw ValidationException::withMessages(['backup' => 'The backup file could not be created.']);
        }

        gzwrite($stream, json_encode(['format' => 'cholavin-erp-backup', 'version' => 1, 'created_at' => now()->toIso8601String()])."\n");
        foreach (self::BACKUP_TABLES as $table) {
            if (! Schema::hasTable($table)) continue;
            DB::table($table)->orderBy('id')->chunk(500, function ($rows) use ($stream, $table) {
                foreach ($rows as $row) {
                    gzwrite($stream, json_encode(['table' => $table, 'row' => (array) $row], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)."\n");
                }
            });
        }
        gzclose($stream);

        return ['name' => $name, 'size' => round(filesize($path) / 1024, 2).' KB'];
    }

    public function downloadBackup(string $name): BinaryFileResponse
    {
        abort_unless($name === basename($name) && preg_match('/^cholavin-erp-[0-9]{8}-[0-9]{6}\.ndjson\.gz$/', $name), 404);
        $path = $this->backupDirectory().DIRECTORY_SEPARATOR.$name;
        abort_unless(File::isFile($path), 404);
        return response()->download($path, $name, ['Content-Type' => 'application/gzip']);
    }

    public function export(string $dataset): BinaryFileResponse
    {
        $config = $this->dataset($dataset);
        $name = $dataset.'-'.now()->format('Ymd-His').'.csv';
        $path = storage_path('app/private/'.$name);
        File::ensureDirectoryExists(dirname($path));
        $stream = fopen($path, 'wb');
        fputcsv($stream, $config['columns']);
        $config['query']()->orderBy('id')->chunk(500, function ($rows) use ($stream, $config) {
            foreach ($rows as $row) fputcsv($stream, collect($config['columns'])->map(fn ($column) => data_get($row, $column))->all());
        });
        fclose($stream);
        return response()->download($path, $name, ['Content-Type' => 'text/csv'])->deleteFileAfterSend(true);
    }

    public function import(string $dataset, UploadedFile $file): int
    {
        $config = $this->dataset($dataset);
        $stream = fopen($file->getRealPath(), 'rb');
        $headers = array_map(fn ($header) => Str::snake(trim((string) $header)), fgetcsv($stream) ?: []);
        $required = $config['required'];
        if (array_diff($required, $headers)) {
            fclose($stream);
            throw ValidationException::withMessages(['file' => 'CSV headings must include: '.implode(', ', $required).'.']);
        }

        $rows = [];
        while (($values = fgetcsv($stream)) !== false) {
            if (count($headers) !== count($values)) continue;
            $row = array_combine($headers, $values);
            if (collect($row)->filter(fn ($value) => $value !== '')->isNotEmpty()) $rows[] = $row;
            if (count($rows) > 5000) throw ValidationException::withMessages(['file' => 'A single import is limited to 5,000 rows.']);
        }
        fclose($stream);

        return DB::transaction(function () use ($rows, $config) {
            foreach ($rows as $index => $row) {
                try {
                    $config['import']($row);
                } catch (\Throwable $exception) {
                    throw ValidationException::withMessages(['file' => 'Row '.($index + 2).': '.$exception->getMessage()]);
                }
            }
            return count($rows);
        });
    }

    public function errorLog(): string
    {
        $path = storage_path('logs/laravel.log');
        if (! File::isFile($path)) return 'No Laravel error log exists.';
        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];
        return implode(PHP_EOL, array_slice($lines, -250));
    }

    private function dataset(string $dataset): array
    {
        $shopId = (int) session('active_shop_id');
        return match ($dataset) {
            'reference-masters' => [
                'columns' => ['type', 'code', 'name', 'description', 'percentage', 'is_active'],
                'required' => ['type', 'code', 'name'],
                'query' => fn () => DB::table('reference_masters')->select(['id', 'type', 'code', 'name', 'description', 'percentage', 'is_active']),
                'import' => fn (array $row) => DB::table('reference_masters')->updateOrInsert(['type' => $row['type'], 'code' => $row['code']], ['name' => $row['name'], 'description' => $row['description'] ?? null, 'percentage' => filled($row['percentage'] ?? null) ? $row['percentage'] : null, 'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOL), 'updated_at' => now(), 'created_at' => now()]),
            ],
            'products' => [
                'columns' => ['sku', 'barcode', 'name', 'purchase_price', 'sale_price', 'reorder_level', 'is_active'],
                'required' => ['sku', 'name'],
                'query' => fn () => DB::table('products')->select(['id', 'sku', 'barcode', 'name', 'purchase_price', 'sale_price', 'reorder_level', 'is_active']),
                'import' => fn (array $row) => DB::table('products')->updateOrInsert(['sku' => $row['sku']], ['slug' => Str::slug($row['name']).'-'.substr(md5($row['sku']), 0, 6), 'barcode' => filled($row['barcode'] ?? null) ? $row['barcode'] : null, 'name' => $row['name'], 'purchase_price' => (float) ($row['purchase_price'] ?? 0), 'sale_price' => (float) ($row['sale_price'] ?? 0), 'reorder_level' => (float) ($row['reorder_level'] ?? 0), 'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOL), 'updated_at' => now(), 'created_at' => now()]),
            ],
            'parties' => [
                'columns' => ['code', 'name', 'type', 'mobile', 'email', 'gstin', 'credit_limit', 'opening_balance', 'balance_type', 'is_active'],
                'required' => ['code', 'name', 'type'],
                'query' => fn () => DB::table('parties')->where('shop_id', $shopId)->select(['id', 'code', 'name', 'type', 'mobile', 'email', 'gstin', 'credit_limit', 'opening_balance', 'balance_type', 'is_active']),
                'import' => fn (array $row) => DB::table('parties')->updateOrInsert(['shop_id' => $shopId, 'code' => $row['code']], ['name' => $row['name'], 'type' => in_array($row['type'], ['customer', 'supplier', 'both'], true) ? $row['type'] : 'customer', 'mobile' => filled($row['mobile'] ?? null) ? $row['mobile'] : null, 'email' => filled($row['email'] ?? null) ? $row['email'] : null, 'gstin' => filled($row['gstin'] ?? null) ? $row['gstin'] : null, 'credit_limit' => (float) ($row['credit_limit'] ?? 0), 'opening_balance' => (float) ($row['opening_balance'] ?? 0), 'balance_type' => ($row['balance_type'] ?? '') === 'payable' ? 'payable' : 'receivable', 'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOL), 'updated_at' => now(), 'created_at' => now()]),
            ],
            default => abort(404),
        };
    }

    private function backupDirectory(): string
    {
        $directory = storage_path('app/private/erp-backups');
        File::ensureDirectoryExists($directory);
        return $directory;
    }
}

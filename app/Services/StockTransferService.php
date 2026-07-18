<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function create(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data): StockTransfer {
            $number = 'TRF-'.now()->format('ym').'-'.str_pad(
                (string) ((int) StockTransfer::where('shop_id', session('active_shop_id'))->lockForUpdate()->max('id') + 1),
                5,
                '0',
                STR_PAD_LEFT,
            );
            $transfer = StockTransfer::create([
                'shop_id' => session('active_shop_id'),
                'financial_year_id' => session('active_financial_year_id'),
                'from_godown_id' => $data['from_godown_id'],
                'to_godown_id' => $data['to_godown_id'],
                'number' => $number,
                'transfer_date' => $data['transfer_date'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $transfer->items()->create($item);
                if ($transfer->status !== 'completed') continue;
                $product = Product::findOrFail($item['product_id']);
                $batch = $item['batch_number'] ?? '';
                $this->inventory->move($transfer->shop_id, $transfer->from_godown_id, $product, -(float) $item['quantity'], 0, 'stock_transfer_out', $transfer->transfer_date, $transfer->number, null, $batch);
                $this->inventory->move($transfer->shop_id, $transfer->to_godown_id, $product, (float) $item['quantity'], 0, 'stock_transfer_in', $transfer->transfer_date, $transfer->number, null, $batch);
            }

            return $transfer->load(['items.product', 'fromGodown', 'toGodown']);
        });
    }
}

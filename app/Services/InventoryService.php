<?php

namespace App\Services;

use App\Models\CommercialDocument;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function applyDocument(CommercialDocument $document, int $effect): void
    {
        if ($effect === 0) return;
        if (! $document->godown_id) {
            throw ValidationException::withMessages(['godown_id' => 'Select a godown before posting a stock-affecting document.']);
        }

        $document->loadMissing('items.product');
        foreach ($document->items as $item) {
            $quantity = $effect * (float) $item->quantity;
            $this->move(
                $document->shop_id,
                $document->godown_id,
                $item->product,
                $quantity,
                (float) $item->rate,
                $document->type,
                $document->document_date,
                $document->number,
                $document->id,
                $item->batch_number ?? '',
                $item->expiry_date?->format('Y-m-d')
            );
        }
    }

    public function move(int $shopId, int $godownId, Product $product, float $quantity, float $rate, string $type, mixed $date, ?string $reference = null, ?int $documentId = null, string $batch = '', ?string $expiry = null): InventoryMovement
    {
        $balance = InventoryBalance::query()->lockForUpdate()->firstOrCreate(
            ['shop_id' => $shopId, 'godown_id' => $godownId, 'product_id' => $product->id, 'batch_number' => $batch],
            ['expiry_date' => $expiry, 'quantity' => 0, 'average_cost' => 0]
        );
        $currentQuantity = (float) $balance->quantity;
        $newQuantity = $currentQuantity + $quantity;
        if ($newQuantity < 0) {
            throw ValidationException::withMessages(['items' => "Insufficient stock for {$product->name}. Available: {$currentQuantity}."]);
        }

        $averageCost = (float) $balance->average_cost;
        if ($quantity > 0 && $newQuantity > 0) {
            $averageCost = (($currentQuantity * $averageCost) + ($quantity * $rate)) / $newQuantity;
        }
        $balance->update(['quantity' => $newQuantity, 'average_cost' => $averageCost, 'expiry_date' => $expiry ?: $balance->expiry_date]);

        return InventoryMovement::create([
            'shop_id' => $shopId, 'godown_id' => $godownId, 'product_id' => $product->id,
            'commercial_document_id' => $documentId, 'type' => $type, 'movement_date' => $date,
            'reference_number' => $reference, 'batch_number' => $batch, 'expiry_date' => $expiry,
            'quantity' => $quantity, 'rate' => $rate, 'value' => $quantity * $rate,
            'created_by' => auth()->id(),
        ]);
    }
}

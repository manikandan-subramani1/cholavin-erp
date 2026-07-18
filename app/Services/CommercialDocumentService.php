<?php

namespace App\Services;

use App\Models\CommercialDocument;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommercialDocumentService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly AccountingPostingService $accounting,
    ) {}

    public function filteredQuery(string $type, Request $request): Builder
    {
        $search = is_array($request->input('search')) ? data_get($request->input('search'), 'value') : $request->input('search');

        return CommercialDocument::query()
            ->with(['party:id,name,code', 'godown:id,name'])
            ->withCount('items')
            ->accessibleBy($request->user())
            ->where('type', $type)
            ->when(session('active_financial_year_id'), fn ($query) => $query->where('financial_year_id', session('active_financial_year_id')))
            ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('number', 'like', "%{$search}%")->orWhere('reference_number', 'like', "%{$search}%")->orWhereHas('party', fn ($query) => $query->where('name', 'like', "%{$search}%"))))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('party_id'), fn ($query) => $query->where('party_id', $request->integer('party_id')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('document_date', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('document_date', '<=', $request->date('to_date')));
    }

    public function create(array $data, array $module): CommercialDocument
    {
        return DB::transaction(function () use ($data, $module) {
            $document = CommercialDocument::create($this->documentPayload($data, $module));
            $totals = $this->syncItems($document, $data['items']);
            $document->update($totals + ['balance_amount' => $totals['total_amount']]);
            if ($document->status === 'posted') {
                $this->inventory->applyDocument($document, (int) $module['stock_effect']);
                $this->accounting->postDocument($document);
            }

            return $document->refresh()->load(['party', 'godown', 'items.product']);
        });
    }

    public function update(CommercialDocument $document, array $data, array $module): CommercialDocument
    {
        if ($document->status === 'posted') {
            throw ValidationException::withMessages(['status' => 'Posted documents cannot be edited. Create a return or adjustment instead.']);
        }

        return DB::transaction(function () use ($document, $data, $module) {
            $document->update($this->documentPayload($data, $module, $document));
            $document->items()->delete();
            $totals = $this->syncItems($document, $data['items']);
            $document->update($totals + ['balance_amount' => $totals['total_amount'] - (float) $document->paid_amount]);
            if ($document->status === 'posted') {
                $this->inventory->applyDocument($document, (int) $module['stock_effect']);
                $this->accounting->postDocument($document);
            }

            return $document->refresh()->load(['party', 'godown', 'items.product']);
        });
    }

    private function documentPayload(array $data, array $module, ?CommercialDocument $document = null): array
    {
        return [
            'shop_id' => session('active_shop_id'), 'godown_id' => session('active_godown_id'),
            'financial_year_id' => $document?->financial_year_id ?? session('active_financial_year_id'),
            'party_id' => $data['party_id'] ?? null, 'type' => $module['type'],
            'number' => $document?->number ?? $this->nextNumber($module['type']),
            'document_date' => $data['document_date'], 'due_date' => $data['due_date'] ?? null,
            'status' => $data['status'], 'reference_number' => $data['reference_number'] ?? null,
            'expense_amount' => $data['expense_amount'] ?? 0,
            'round_off' => $data['round_off'] ?? 0, 'notes' => $data['notes'] ?? null,
            'created_by' => $document?->created_by ?? auth()->id(),
        ];
    }

    private function syncItems(CommercialDocument $document, array $items): array
    {
        $subtotal = $discount = $tax = 0;
        $products = Product::query()->with('taxRate:id,percentage')->whereIn('id', collect($items)->pluck('product_id'))->get()->keyBy('id');
        foreach ($items as $item) {
            $product = $products->get((int) $item['product_id']);
            abort_unless($product, 422, 'A selected product is unavailable.');
            $quantity = round((float) $item['quantity'], 3);
            $rate = round((float) $item['rate'], 2);
            $lineBase = round($quantity * $rate, 2);
            $lineDiscount = min(round((float) ($item['discount_amount'] ?? 0), 2), $lineBase);
            $taxRate = (float) ($product->taxRate?->percentage ?? 0);
            $lineTax = round(($lineBase - $lineDiscount) * $taxRate / 100, 2);
            $lineTotal = $lineBase - $lineDiscount + $lineTax;
            $document->items()->create([
                'product_id' => $product->id, 'description' => $item['description'] ?? $product->name,
                'quantity' => $quantity, 'unit' => $item['unit'] ?? $product->unit,
                'rate' => $rate, 'discount_amount' => $lineDiscount, 'tax_rate' => $taxRate,
                'tax_amount' => $lineTax, 'line_total' => $lineTotal,
                'batch_number' => $item['batch_number'] ?? null, 'expiry_date' => $item['expiry_date'] ?? null,
            ]);
            $subtotal += $lineBase; $discount += $lineDiscount; $tax += $lineTax;
        }
        $total = round($subtotal - $discount + $tax + (float) $document->expense_amount + (float) $document->round_off, 2);
        return ['subtotal' => $subtotal, 'discount_amount' => $discount, 'tax_amount' => $tax, 'total_amount' => $total];
    }

    private function nextNumber(string $type): string
    {
        $prefix = str($type)->upper()->replace('_', '')->substr(0, 4)->toString();
        $lastId = (int) CommercialDocument::query()
            ->where('shop_id', session('active_shop_id'))
            ->where('financial_year_id', session('active_financial_year_id'))
            ->where('type', $type)
            ->lockForUpdate()
            ->max('id');
        return $prefix.'-'.now()->format('ym').'-'.str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}



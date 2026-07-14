<?php

namespace App\Services;

use App\Models\CommercialDocument;
use App\Models\LedgerAccount;
use App\Models\Payment;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingPostingService
{
    public function postDocument(CommercialDocument $document): ?Voucher
    {
        $mapping = match ($document->type) {
            'sales_invoice', 'pos_invoice' => ['AR', 'SALES'],
            'sales_return', 'credit_note' => ['SALES_RETURN', 'AR'],
            'purchase_bill' => ['PURCHASE', 'AP'],
            'purchase_return', 'debit_note' => ['AP', 'PURCHASE_RETURN'],
            default => null,
        };

        if (! $mapping || $document->status !== 'posted') {
            return null;
        }

        return $this->post(
            shopId: $document->shop_id,
            type: 'system_document',
            date: $document->document_date,
            reference: $document->number,
            narration: str($document->type)->replace('_', ' ')->title().' '.$document->number,
            sourceType: 'commercial_document',
            sourceId: $document->id,
            debitCode: $mapping[0],
            creditCode: $mapping[1],
            amount: (float) $document->total_amount,
            partyId: $document->party_id,
        );
    }

    public function postPayment(Payment $payment): Voucher
    {
        $methodCode = strtoupper((string) $payment->method?->code);
        $cashCode = str_contains($methodCode, 'BANK') || str_contains($methodCode, 'UPI') ? 'BANK' : 'CASH';
        [$debit, $credit] = match ($payment->type) {
            'customer_collection', 'cash_receipt', 'bank_receipt' => [$cashCode, 'AR'],
            'supplier_payment', 'cash_payment', 'bank_payment' => ['AP', $cashCode],
            'income' => [$cashCode, 'OTHER_INCOME'],
            'expense' => ['EXPENSE', $cashCode],
            default => throw ValidationException::withMessages(['type' => 'Unsupported accounting payment type.']),
        };

        return $this->post(
            shopId: $payment->shop_id,
            type: 'system_payment',
            date: $payment->payment_date,
            reference: $payment->number,
            narration: str($payment->type)->replace('_', ' ')->title().' '.$payment->number,
            sourceType: 'payment',
            sourceId: $payment->id,
            debitCode: $debit,
            creditCode: $credit,
            amount: (float) $payment->amount,
            partyId: $payment->party_id,
        );
    }

    private function post(int $shopId, string $type, mixed $date, string $reference, string $narration, string $sourceType, int $sourceId, string $debitCode, string $creditCode, float $amount, ?int $partyId): Voucher
    {
        return DB::transaction(function () use ($shopId, $type, $date, $reference, $narration, $sourceType, $sourceId, $debitCode, $creditCode, $amount, $partyId) {
            $existing = Voucher::query()->where('source_type', $sourceType)->where('source_id', $sourceId)->first();
            if ($existing) {
                return $existing;
            }

            $accounts = LedgerAccount::query()
                ->whereIn('code', [$debitCode, $creditCode])
                ->where(fn ($query) => $query->whereNull('shop_id')->orWhere('shop_id', $shopId))
                ->get()->keyBy('code');

            if (! $accounts->has($debitCode) || ! $accounts->has($creditCode)) {
                throw ValidationException::withMessages(['accounts' => "Ledger setup is incomplete ({$debitCode}/{$creditCode}). Run the ERP foundation seeder."]);
            }

            $next = (int) Voucher::query()->where('shop_id', $shopId)->lockForUpdate()->max('id') + 1;
            $voucher = Voucher::create([
                'shop_id' => $shopId,
                'type' => $type,
                'number' => 'AUTO-'.now()->format('ym').'-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT),
                'voucher_date' => $date,
                'reference_number' => $reference,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'narration' => $narration,
                'total_debit' => $amount,
                'total_credit' => $amount,
                'created_by' => auth()->id(),
            ]);
            $voucher->lines()->createMany([
                ['ledger_account_id' => $accounts[$debitCode]->id, 'party_id' => $partyId, 'debit' => $amount, 'credit' => 0, 'description' => $narration],
                ['ledger_account_id' => $accounts[$creditCode]->id, 'party_id' => $partyId, 'debit' => 0, 'credit' => $amount, 'description' => $narration],
            ]);

            return $voucher;
        });
    }
}

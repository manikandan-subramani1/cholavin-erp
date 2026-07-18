<?php

namespace App\Services;

use App\Models\CommercialDocument;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private readonly AccountingPostingService $accounting) {}

    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data): Payment {
            $shopId = (int) session('active_shop_id');
            $financialYearId = (int) session('active_financial_year_id');
            $lastId = (int) Payment::query()
                ->where('shop_id', $shopId)
                ->where('financial_year_id', $financialYearId)
                ->lockForUpdate()
                ->max('id');

            $payment = Payment::create($data + [
                'shop_id' => $shopId,
                'godown_id' => session('active_godown_id'),
                'financial_year_id' => $financialYearId,
                'number' => 'PAY-'.now()->format('ym').'-'.str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT),
                'created_by' => auth()->id(),
            ]);

            if ($payment->commercial_document_id) {
                $document = CommercialDocument::query()->lockForUpdate()->findOrFail($payment->commercial_document_id);
                abort_unless(
                    $document->shop_id === $shopId && $document->financial_year_id === $financialYearId,
                    404
                );
                $paid = (float) $document->paid_amount + (float) $payment->amount;
                if ($paid > (float) $document->total_amount) {
                    throw ValidationException::withMessages(['amount' => 'Payment exceeds the document balance.']);
                }
                $document->update([
                    'paid_amount' => $paid,
                    'balance_amount' => (float) $document->total_amount - $paid,
                ]);
            }

            $payment->load(['party', 'document', 'method']);
            $this->accounting->postPayment($payment);

            return $payment;
        });
    }
}

<?php

namespace App\Services;

use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function create(array $data): Voucher
    {
        return DB::transaction(function () use ($data): Voucher {
            $lines = $data['lines'];
            unset($data['lines']);
            $shopId = (int) session('active_shop_id');
            $financialYearId = (int) session('active_financial_year_id');
            $total = collect($lines)->sum(fn ($line) => (float) ($line['debit'] ?? 0));
            $lastId = (int) Voucher::query()
                ->where('shop_id', $shopId)
                ->where('financial_year_id', $financialYearId)
                ->lockForUpdate()
                ->max('id');

            $voucher = Voucher::create($data + [
                'shop_id' => $shopId,
                'financial_year_id' => $financialYearId,
                'number' => 'VCH-'.now()->format('ym').'-'.str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT),
                'total_debit' => $total,
                'total_credit' => $total,
                'created_by' => auth()->id(),
            ]);
            $voucher->lines()->createMany($lines);

            return $voucher->load('lines.account');
        });
    }
}

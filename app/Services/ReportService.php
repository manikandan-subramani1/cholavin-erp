<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public const REPORTS = [
        'sales' => 'Sales Report',
        'purchases' => 'Purchase Report',
        'stock' => 'Stock Movement Ledger',
        'stock-valuation' => 'Stock Valuation',
        'low-stock' => 'Low-stock Report',
        'expiry' => 'Batch Expiry Report',
        'parties' => 'Party Balance Report',
        'party-ledger' => 'Party Ledger',
        'receivables' => 'Receivables',
        'payables' => 'Payables',
        'payments' => 'Payment Report',
        'expenses' => 'Expense Report',
        'day-book' => 'Day Book',
        'cash-book' => 'Cash Book',
        'bank-book' => 'Bank Book',
        'general-ledger' => 'General Ledger',
        'gst' => 'GST Report',
        'profit-loss' => 'Profit & Loss',
        'audit' => 'Audit Report',
        'user-activity' => 'User Activity Report',
    ];

    public function query(string $report, Request $request): Builder
    {
        abort_unless(isset(self::REPORTS[$report]), 404);
        $shopId = (int) session('active_shop_id');
        $godownId = (int) session('active_godown_id');

        $source = match ($report) {
            'sales' => $this->documents($shopId, ['sales_invoice', 'pos_invoice', 'sales_return', 'credit_note']),
            'purchases' => $this->documents($shopId, ['purchase_bill', 'goods_receipt', 'purchase_return', 'debit_note']),
            'gst' => $this->gst($shopId),
            'stock' => $this->stockMovements($shopId, $godownId),
            'stock-valuation' => $this->stockBalances($shopId, $godownId, false, false),
            'low-stock' => $this->stockBalances($shopId, $godownId, true, false),
            'expiry' => $this->stockBalances($shopId, $godownId, false, true),
            'parties' => $this->partyBalances($shopId),
            'receivables' => $this->outstandings($shopId, 'customer'),
            'payables' => $this->outstandings($shopId, 'supplier'),
            'party-ledger' => $this->partyLedger($shopId, $request->integer('party_id') ?: null),
            'payments' => $this->payments($shopId),
            'expenses' => $this->ledger($shopId, accountType: 'expense'),
            'day-book' => $this->dayBook($shopId),
            'cash-book' => $this->ledger($shopId, accountCode: 'CASH'),
            'bank-book' => $this->ledger($shopId, accountCode: 'BANK'),
            'general-ledger' => $this->ledger($shopId),
            'profit-loss' => $this->profitLoss($shopId),
            'audit', 'user-activity' => $this->activity($shopId),
        };

        $search = is_array($request->input('search'))
            ? data_get($request->input('search'), 'value')
            : $request->input('search');

        return DB::query()->fromSub($source, 'report_rows')
            ->when($request->filled('from_date'), fn (Builder $query) => $query->whereDate('report_date', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn (Builder $query) => $query->whereDate('report_date', '<=', $request->date('to_date')))
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('reference', 'like', "%{$search}%")
                ->orWhere('party', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")));
    }

    private function documents(int $shopId, array $types): Builder
    {
        return DB::table('commercial_documents as documents')
            ->leftJoin('parties', 'parties.id', '=', 'documents.party_id')
            ->where('documents.shop_id', $shopId)
            ->whereIn('documents.type', $types)
            ->selectRaw("documents.document_date as report_date, documents.number as reference, COALESCE(parties.name, 'Cash') as party, documents.type as description, documents.subtotal as debit, documents.tax_amount as credit, documents.total_amount as amount, documents.status as status");
    }

    private function gst(int $shopId): Builder
    {
        return DB::table('commercial_documents as documents')
            ->leftJoin('parties', 'parties.id', '=', 'documents.party_id')
            ->where('documents.shop_id', $shopId)
            ->whereIn('documents.type', ['sales_invoice', 'pos_invoice', 'sales_return', 'credit_note', 'purchase_bill', 'purchase_return', 'debit_note'])
            ->selectRaw("documents.document_date as report_date, documents.number as reference, COALESCE(parties.name, 'Cash') as party, documents.type as description, documents.subtotal - documents.discount_amount as debit, documents.tax_amount as credit, documents.total_amount as amount, documents.status as status");
    }

    private function stockMovements(int $shopId, int $godownId): Builder
    {
        return DB::table('inventory_movements as movements')
            ->join('products', 'products.id', '=', 'movements.product_id')
            ->where('movements.shop_id', $shopId)
            ->when($godownId, fn (Builder $query) => $query->where('movements.godown_id', $godownId))
            ->selectRaw("movements.movement_date as report_date, COALESCE(movements.reference_number, '') as reference, products.name as party, movements.type as description, CASE WHEN movements.quantity > 0 THEN movements.quantity ELSE 0 END as debit, CASE WHEN movements.quantity < 0 THEN ABS(movements.quantity) ELSE 0 END as credit, movements.value as amount, 'posted' as status");
    }

    private function stockBalances(int $shopId, int $godownId, bool $lowStock, bool $expiry): Builder
    {
        return DB::table('inventory_balances as balances')
            ->join('products', 'products.id', '=', 'balances.product_id')
            ->join('godowns', 'godowns.id', '=', 'balances.godown_id')
            ->where('balances.shop_id', $shopId)
            ->when($godownId, fn (Builder $query) => $query->where('balances.godown_id', $godownId))
            ->when($lowStock, fn (Builder $query) => $query->whereColumn('balances.quantity', '<=', 'products.reorder_level'))
            ->when($expiry, fn (Builder $query) => $query->whereNotNull('balances.expiry_date')->where('balances.expiry_date', '<=', now()->addDays(90)->toDateString()))
            ->selectRaw("COALESCE(balances.expiry_date, DATE(balances.updated_at)) as report_date, COALESCE(balances.batch_number, '') as reference, products.name as party, godowns.name as description, balances.quantity as debit, products.reorder_level as credit, balances.quantity * balances.average_cost as amount, CASE WHEN balances.quantity <= products.reorder_level THEN 'reorder' ELSE 'available' END as status");
    }

    private function partyBalances(int $shopId): Builder
    {
        return DB::table('parties')
            ->where('parties.shop_id', $shopId)
            ->selectRaw("DATE(parties.created_at) as report_date, parties.code as reference, parties.name as party, parties.type as description, CASE WHEN parties.balance_type = 'receivable' THEN parties.opening_balance ELSE 0 END as debit, CASE WHEN parties.balance_type = 'payable' THEN parties.opening_balance ELSE 0 END as credit, parties.opening_balance as amount, CASE WHEN parties.is_active = 1 THEN 'active' ELSE 'inactive' END as status");
    }

    private function outstandings(int $shopId, string $partyType): Builder
    {
        $types = $partyType === 'customer'
            ? ['sales_invoice', 'pos_invoice', 'sales_return', 'credit_note']
            : ['purchase_bill', 'purchase_return', 'debit_note'];

        return DB::table('commercial_documents as documents')
            ->join('parties', 'parties.id', '=', 'documents.party_id')
            ->where('documents.shop_id', $shopId)
            ->whereIn('documents.type', $types)
            ->where('documents.status', 'posted')
            ->where('documents.balance_amount', '>', 0)
            ->selectRaw("COALESCE(documents.due_date, documents.document_date) as report_date, documents.number as reference, parties.name as party, documents.type as description, documents.total_amount as debit, documents.paid_amount as credit, documents.balance_amount as amount, CASE WHEN documents.due_date IS NOT NULL AND documents.due_date < CURRENT_DATE THEN 'overdue' ELSE 'outstanding' END as status");
    }

    private function partyLedger(int $shopId, ?int $partyId): Builder
    {
        $documents = DB::table('commercial_documents as documents')
            ->join('parties', 'parties.id', '=', 'documents.party_id')
            ->where('documents.shop_id', $shopId)
            ->where('documents.status', 'posted')
            ->when($partyId, fn (Builder $query) => $query->where('documents.party_id', $partyId))
            ->selectRaw("documents.document_date as report_date, documents.number as reference, parties.name as party, documents.type as description, documents.total_amount as debit, documents.paid_amount as credit, documents.balance_amount as amount, documents.status as status");

        $payments = DB::table('payments')
            ->join('parties', 'parties.id', '=', 'payments.party_id')
            ->where('payments.shop_id', $shopId)
            ->when($partyId, fn (Builder $query) => $query->where('payments.party_id', $partyId))
            ->selectRaw("payments.payment_date as report_date, payments.number as reference, parties.name as party, payments.type as description, CASE WHEN payments.type = 'supplier_payment' THEN payments.amount ELSE 0 END as debit, CASE WHEN payments.type = 'customer_collection' THEN payments.amount ELSE 0 END as credit, payments.amount as amount, 'posted' as status");

        return $documents->unionAll($payments);
    }

    private function payments(int $shopId): Builder
    {
        return DB::table('payments')
            ->leftJoin('parties', 'parties.id', '=', 'payments.party_id')
            ->where('payments.shop_id', $shopId)
            ->selectRaw("payments.payment_date as report_date, payments.number as reference, COALESCE(parties.name, '') as party, payments.type as description, CASE WHEN payments.type LIKE '%receipt%' OR payments.type IN ('income', 'customer_collection') THEN payments.amount ELSE 0 END as debit, CASE WHEN payments.type LIKE '%payment%' OR payments.type = 'expense' THEN payments.amount ELSE 0 END as credit, payments.amount as amount, 'posted' as status");
    }

    private function dayBook(int $shopId): Builder
    {
        return DB::table('vouchers')
            ->where('vouchers.shop_id', $shopId)
            ->selectRaw("vouchers.voucher_date as report_date, vouchers.number as reference, '' as party, COALESCE(vouchers.narration, vouchers.type) as description, vouchers.total_debit as debit, vouchers.total_credit as credit, vouchers.total_debit as amount, 'posted' as status");
    }

    private function ledger(int $shopId, ?string $accountCode = null, ?string $accountType = null): Builder
    {
        return DB::table('voucher_lines as lines')
            ->join('vouchers', 'vouchers.id', '=', 'lines.voucher_id')
            ->join('ledger_accounts as accounts', 'accounts.id', '=', 'lines.ledger_account_id')
            ->leftJoin('parties', 'parties.id', '=', 'lines.party_id')
            ->where('vouchers.shop_id', $shopId)
            ->when($accountCode, fn (Builder $query) => $query->where('accounts.code', $accountCode))
            ->when($accountType, fn (Builder $query) => $query->where('accounts.type', $accountType))
            ->selectRaw("vouchers.voucher_date as report_date, vouchers.number as reference, COALESCE(parties.name, accounts.name) as party, accounts.name as description, lines.debit as debit, lines.credit as credit, lines.debit - lines.credit as amount, 'posted' as status");
    }

    private function profitLoss(int $shopId): Builder
    {
        return DB::table('voucher_lines as lines')
            ->join('vouchers', 'vouchers.id', '=', 'lines.voucher_id')
            ->join('ledger_accounts as accounts', 'accounts.id', '=', 'lines.ledger_account_id')
            ->where('vouchers.shop_id', $shopId)
            ->whereIn('accounts.type', ['income', 'expense'])
            ->selectRaw("vouchers.voucher_date as report_date, vouchers.number as reference, accounts.name as party, accounts.type as description, CASE WHEN accounts.type = 'expense' THEN lines.debit - lines.credit ELSE 0 END as debit, CASE WHEN accounts.type = 'income' THEN lines.credit - lines.debit ELSE 0 END as credit, CASE WHEN accounts.type = 'income' THEN lines.credit - lines.debit ELSE -(lines.debit - lines.credit) END as amount, 'posted' as status");
    }

    private function activity(int $shopId): Builder
    {
        return DB::table('activity_logs')
            ->leftJoin('users', 'users.id', '=', 'activity_logs.user_id')
            ->when($shopId, fn (Builder $query) => $query->where(fn (Builder $query) => $query->whereNull('activity_logs.shop_id')->orWhere('activity_logs.shop_id', $shopId)))
            ->selectRaw("DATE(activity_logs.created_at) as report_date, COALESCE(activity_logs.route, '') as reference, COALESCE(users.name, 'System') as party, activity_logs.event as description, 0 as debit, 0 as credit, 0 as amount, COALESCE(activity_logs.action, 'logged') as status");
    }
}

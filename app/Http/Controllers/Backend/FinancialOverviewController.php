<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FinancialOverviewController extends Controller
{
    public function __invoke(Request $request): View
    {
        Gate::authorize('reports.view');

        $shopId = session('active_shop_id') ? (int) session('active_shop_id') : null;
        $godownId = session('active_godown_id') ? (int) session('active_godown_id') : null;
        $financialYearId = session('active_financial_year_id') ? (int) session('active_financial_year_id') : null;
        $denyUnscoped = ! $shopId && ! $request->user()->isSuperAdmin();
        $from = CarbonImmutable::today()->startOfMonth()->subMonths(11);

        $ledger = DB::table('voucher_lines as lines')
            ->join('vouchers', 'vouchers.id', '=', 'lines.voucher_id')
            ->join('ledger_accounts as accounts', 'accounts.id', '=', 'lines.ledger_account_id')
            ->when($shopId, fn (Builder $query) => $query->where('vouchers.shop_id', $shopId))
            ->when($denyUnscoped, fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->when($financialYearId, fn (Builder $query) => $query->where('vouchers.financial_year_id', $financialYearId))
            ->whereIn('accounts.type', ['income', 'expense'])
            ->whereDate('vouchers.voucher_date', '>=', $from->toDateString())
            ->get(['vouchers.voucher_date', 'accounts.type', 'lines.debit', 'lines.credit']);

        $income = (float) $ledger->where('type', 'income')->sum(fn ($line) => (float) $line->credit - (float) $line->debit);
        $expenses = (float) $ledger->where('type', 'expense')->sum(fn ($line) => (float) $line->debit - (float) $line->credit);

        $documents = DB::table('commercial_documents')
            ->when($shopId, fn (Builder $query) => $query->where('shop_id', $shopId))
            ->when($denyUnscoped, fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->when($godownId, fn (Builder $query) => $query->where('godown_id', $godownId))
            ->when($financialYearId, fn (Builder $query) => $query->where('financial_year_id', $financialYearId))
            ->where('status', 'posted');

        $stock = DB::table('inventory_balances')
            ->when($shopId, fn (Builder $query) => $query->where('shop_id', $shopId))
            ->when($denyUnscoped, fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->when($godownId, fn (Builder $query) => $query->where('godown_id', $godownId));

        $months = collect(range(0, 11))->map(fn (int $offset) => $from->addMonths($offset));
        $monthly = $ledger->groupBy(fn ($line) => CarbonImmutable::parse($line->voucher_date)->format('Y-m'));

        return view('backend.pages.financial-overview', [
            'summary' => [
                'income' => $income,
                'expenses' => $expenses,
                'net_profit' => $income - $expenses,
                'receivables' => (float) (clone $documents)->whereIn('type', ['sales_invoice', 'pos_invoice'])->sum('balance_amount'),
                'payables' => (float) (clone $documents)->where('type', 'purchase_bill')->sum('balance_amount'),
                'stock_value' => (float) (clone $stock)->sum(DB::raw('quantity * average_cost')),
            ],
            'chartData' => [
                'labels' => $months->map(fn (CarbonImmutable $month) => $month->format('M Y'))->values(),
                'income' => $months->map(fn (CarbonImmutable $month) => round((float) $monthly->get($month->format('Y-m'), collect())->where('type', 'income')->sum(fn ($line) => (float) $line->credit - (float) $line->debit), 2))->values(),
                'expenses' => $months->map(fn (CarbonImmutable $month) => round((float) $monthly->get($month->format('Y-m'), collect())->where('type', 'expense')->sum(fn ($line) => (float) $line->debit - (float) $line->credit), 2))->values(),
            ],
        ]);
    }
}

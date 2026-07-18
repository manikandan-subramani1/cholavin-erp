<?php

namespace App\Services;

use App\Models\ReferenceMaster;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DashboardAnalyticsService
{
    private const SALES = ['sales_invoice', 'pos_invoice'];

    private const PURCHASES = ['purchase_bill', 'goods_receipt'];

    public function chartNames(): array
    {
        return ['sales-purchase', 'collections', 'profit', 'expenses', 'shop-comparison',
            'godown-comparison', 'payment-methods', 'receivable-ageing', 'payable-ageing'];
    }

    public function shell(User $user): array
    {
        $scope = $this->scope($user, []);
        $actions = [
            ['New Sale', 'Create customer invoice', 'ri-shopping-cart-2-line', 'sales-invoices.create', route('admin.documents.index', 'sales-invoices'), true],
            ['New Purchase', 'Record supplier bill', 'ri-shopping-bag-3-line', 'purchase-bills.create', route('admin.documents.index', 'purchase-bills'), true],
            ['Receive Payment', 'Customer collection', 'ri-hand-coin-line', 'payments.create', route('admin.payments.index', ['type' => 'customer_collection']), true],
            ['Pay Supplier', 'Supplier payment', 'ri-secure-payment-line', 'payments.create', route('admin.payments.index', ['type' => 'supplier_payment']), true],
            ['Stock Transfer', 'Move inventory', 'ri-arrow-left-right-line', 'stock-transfers.create', route('admin.stock-transfers.index'), true],
            ['Add Customer', 'Create customer', 'ri-user-add-line', 'customers.create', route('admin.parties.index', 'customers'), false],
            ['Add Supplier', 'Create supplier', 'ri-user-star-line', 'suppliers.create', route('admin.parties.index', 'suppliers'), false],
            ['Add Item', 'Create rice product', 'ri-add-box-line', 'products.create', route('admin.products.create'), false],
            ['Add Expense', 'Record expense', 'ri-wallet-3-line', 'payments.create', route('admin.payments.index', ['type' => 'expense']), true],
            ['Low Stock', 'Review reorder list', 'ri-alarm-warning-line', 'stock.view', route('admin.stock.index', ['stock_status' => 'low']), false],
            ['Pending Delivery', 'Open delivery queue', 'ri-truck-line', 'deliveries.view', route('admin.deliveries.index', ['status' => 'pending']), false],
            ['Daily Report', 'Download daily sales', 'ri-download-2-line', 'reports.export', route('admin.reports.index', 'sales'), false],
        ];

        return [
            'mode' => $scope['mode'],
            'mode_label' => match ($scope['mode']) {
                'company' => 'Company overview', 'godown' => 'Godown overview', default => 'Shop overview'
            },
            'role' => $user->role?->name ?? 'User', 'has_financial_year' => (bool) $scope['financial_year_id'], 'has_shop' => (bool) $scope['shop_id'],
            'actions' => collect($actions)->filter(fn ($a) => Gate::forUser($user)->allows($a[3]))->map(fn ($a) => [
                'label' => $a[0], 'description' => $a[1], 'icon' => $a[2], 'url' => $a[4], 'requires_shop' => $a[5],
            ])->values()->all(),
            'tabs' => collect([
                'overview' => ['Overview', true], 'sales' => ['Sales', $user->can('sales-invoices.view') || $user->can('reports.view')],
                'purchases' => ['Purchases', $user->can('purchase-bills.view') || $user->can('reports.view')],
                'inventory' => ['Inventory', $user->can('stock.view') || $user->can('reports.view')],
                'finance' => ['Finance', $user->can('accounts.view') || $user->can('reports.view')],
                'collections' => ['Collections', $user->can('payments.view') || $user->can('reports.view')],
                'deliveries' => ['Deliveries', $user->can('deliveries.view')], 'alerts' => ['Alerts', true], 'activity' => ['Activity', true],
            ])->filter(fn ($item) => $item[1])->map(fn ($item) => $item[0])->all(),
            'endpoints' => ['kpis' => route('admin.dashboard.kpis'), 'chart' => route('admin.dashboard.chart', '__chart__'),
                'top' => route('admin.dashboard.top', '__type__'), 'alerts' => route('admin.dashboard.alerts'),
                'activity' => route('admin.dashboard.activity'), 'tab' => route('admin.dashboard.tab', '__tab__')],
        ];
    }

    public function meta(User $user, array $filters): array
    {
        $s = isset($filters['from']) ? $filters : $this->scope($user, $filters);
        $meta = collect($s)->only(['mode', 'godown_id', 'shop_id', 'financial_year_id', 'date_from', 'date_to', 'preset'])->all();
        $meta['scope'] = $meta['mode'];

        return ['meta' => $meta];
    }

    public function kpis(User $user, array $filters): array
    {
        $s = $this->scope($user, $filters);

        return $this->cached($user, $s, 'kpis', function () use ($user, $s) {
            $current = $this->totals($s, $s['from'], $s['to']);
            $days = $s['from']->diffInDays($s['to']) + 1;
            $previousTo = $s['from']->subDay();
            $previousFrom = $previousTo->subDays($days - 1);
            $previous = $this->totals($s, $previousFrom, $previousTo);
            $stock = $this->stockSummary($s);
            $gross = $current['sales'] - $this->productCost($s, $s['from'], $s['to']);
            $previousGross = $previous['sales'] - $this->productCost($s, $previousFrom, $previousTo);
            $defs = [
                ['sales', 'Today’s Sales', $current['sales'], $previous['sales'], 'money', 'ri-line-chart-line', 'green', 'sales-invoices.view', route('admin.documents.index', ['sales-invoices', 'from_date' => $s['date_from'], 'to_date' => $s['date_to']]), 'Posted sales less returns for this period.'],
                ['purchases', 'Today’s Purchases', $current['purchases'], $previous['purchases'], 'money', 'ri-shopping-bag-3-line', 'blue', 'purchase-bills.view', route('admin.documents.index', ['purchase-bills', 'from_date' => $s['date_from'], 'to_date' => $s['date_to']]), 'Posted purchases less returns.'],
                ['collections', 'Today’s Collections', $current['collections'], $previous['collections'], 'money', 'ri-hand-coin-line', 'purple', 'payments.view', route('admin.payments.index', ['type' => 'customer_collection']), 'Customer collections received.'],
                ['expenses', 'Today’s Expenses', $current['expenses'], $previous['expenses'], 'money', 'ri-wallet-3-line', 'orange', 'payments.view', route('admin.payments.index', ['type' => 'expense']), 'Operating expense payments.'],
                ['gross_profit', 'Gross Profit', $gross, $previousGross, 'money', 'ri-funds-line', 'gold', 'reports.view', route('admin.reports.index', 'profit-loss'), 'Net sales less estimated product cost.'],
                ['net_profit', 'Net Profit', $gross - $current['expenses'], $previousGross - $previous['expenses'], 'money', 'ri-pie-chart-2-line', 'maroon', 'reports.view', route('admin.reports.index', 'profit-loss'), 'Gross profit less operating expenses.'],
                ['receivable', 'Customer Receivable', $current['receivable'], $previous['receivable'], 'money', 'ri-refund-line', 'orange', 'accounts.view', route('admin.reports.index', 'receivables'), 'Outstanding customer invoice balance.'],
                ['payable', 'Supplier Payable', $current['payable'], $previous['payable'], 'money', 'ri-secure-payment-line', 'blue', 'accounts.view', route('admin.reports.index', 'payables'), 'Outstanding supplier bill balance.'],
                ['cash', 'Cash in Hand', $current['cash'], $previous['cash'], 'money', 'ri-cash-line', 'green', 'accounts.view', route('admin.reports.index', 'cash-book'), 'Net recorded cash movement.'],
                ['bank', 'Bank Balance', $current['bank'], $previous['bank'], 'money', 'ri-bank-line', 'blue', 'accounts.view', route('admin.reports.index', 'bank-book'), 'Net recorded bank movement.'],
                ['stock', 'Total Stock Value', $stock['value'], null, 'money', 'ri-stack-line', 'purple', 'stock.view', route('admin.stock.index'), 'On-hand stock at average cost.'],
                ['low_stock', 'Low Stock Items', $stock['low'], null, 'number', 'ri-alarm-warning-line', 'orange', 'stock.view', route('admin.stock.index', ['stock_status' => 'low']), 'Products at or below reorder level.'],
                ['transfers', 'Pending Stock Transfers', $current['transfers'], $previous['transfers'], 'number', 'ri-arrow-left-right-line', 'maroon', 'stock-transfers.view', route('admin.stock-transfers.index'), 'Transfers awaiting completion.'],
                ['deliveries', 'Pending Deliveries', $current['deliveries'], $previous['deliveries'], 'number', 'ri-truck-line', 'purple', 'deliveries.view', route('admin.deliveries.index', ['status' => 'pending']), 'Deliveries awaiting final outcome.'],
                ['sales_returns', 'Sales Returns', $current['sales_returns'], $previous['sales_returns'], 'money', 'ri-arrow-go-back-line', 'red', 'sales-returns.view', route('admin.documents.index', 'sales-returns'), 'Posted sales returns.'],
                ['purchase_returns', 'Purchase Returns', $current['purchase_returns'], $previous['purchase_returns'], 'money', 'ri-arrow-turn-back-line', 'red', 'purchase-returns.view', route('admin.documents.index', 'purchase-returns'), 'Posted purchase returns.'],
            ];
            $cards = collect($defs)->filter(fn ($d) => Gate::forUser($user)->allows($d[7]))->map(function ($d) {
                [$key,$title,$value,$prior,$format,$icon,$tone,$ability,$url,$tooltip] = $d;
                $change = $prior !== null && abs((float) $prior) > .009 ? round((($value - $prior) / abs($prior)) * 100, 1) : null;

                return compact('key', 'title', 'value', 'format', 'icon', 'tone', 'change', 'url', 'tooltip') + ['comparison_label' => 'vs previous period'];
            })->values()->all();

            return ['cards' => $cards] + $this->meta($user, $s);
        });
    }

    public function chart(User $user, array $filters, string $name): array
    {
        $this->authorizeComponent($user, $name);
        $s = $this->scope($user, $filters);

        return $this->cached($user, $s, 'chart-'.$name, fn () => ['chart' => match ($name) {
            'sales-purchase' => $this->documentTrend($s), 'collections' => $this->paymentTrend($s, 'customer_collection', 'Collections'),
            'profit' => $this->profitTrend($s), 'expenses' => $this->expenseCategories($s),
            'shop-comparison' => $this->locationComparison($s, 'shop'), 'godown-comparison' => $this->locationComparison($s, 'godown'),
            'payment-methods' => $this->paymentMethods($s), 'receivable-ageing' => $this->ageing($s, self::SALES, 'Receivable'),
            'payable-ageing' => $this->ageing($s, self::PURCHASES, 'Payable'),
        }] + $this->meta($user, $s));
    }

    public function top(User $user, array $filters, string $type): array
    {
        $this->authorizeComponent($user, 'top-'.$type);
        $s = $this->scope($user, $filters);

        return $this->cached($user, $s, 'top-'.$type, fn () => ['items' => $type === 'products' ? $this->topProducts($s) : $this->topParties($s, $type)] + $this->meta($user, $s));
    }

    public function alerts(User $user, array $filters): array
    {
        $s = $this->scope($user, $filters);

        return $this->cached($user, $s, 'alerts', function () use ($user, $s) {
            $stock = $this->stockSummary($s);
            $counts = $this->alertCounts($s);
            $items = [];
            $add = function ($key, $title, $count, $message, $tone, $url) use (&$items) {
                if ($count > 0) {
                    $items[] = compact('key', 'title', 'count', 'message', 'tone', 'url');
                }
            };
            if ($user->can('stock.view')) {
                $add('low_stock', 'Low stock', $stock['low'], 'Products have reached reorder level.', 'warning', route('admin.stock.index', ['stock_status' => 'low']));
                $add('out_of_stock', 'Out of stock', $stock['out'], 'Products have no available quantity.', 'danger', route('admin.stock.index', ['stock_status' => 'out']));
            }
            $map = [
                ['overdue_receivable', 'Overdue customer payments', 'accounts.view', 'danger', route('admin.reports.index', 'receivables')],
                ['supplier_due', 'Supplier payments due', 'accounts.view', 'warning', route('admin.reports.index', 'payables')],
                ['transfer_approval', 'Transfers pending approval', 'stock-transfers.view', 'warning', route('admin.stock-transfers.index')],
                ['transfer_receipt', 'Transfers awaiting receipt', 'stock-transfers.view', 'info', route('admin.stock-transfers.index')],
                ['pending_delivery', 'Pending deliveries', 'deliveries.view', 'warning', route('admin.deliveries.index', ['status' => 'pending'])],
                ['failed_delivery', 'Failed deliveries', 'deliveries.view', 'danger', route('admin.deliveries.index', ['status' => 'failed'])],
            ];
            foreach ($map as [$key,$title,$ability,$tone,$url]) {
                if ($user->can($ability)) {
                    $add($key, $title, $counts[$key], 'Requires attention in the selected scope.', $tone, $url);
                }
            }
            if ((bool) ($s['financial_year']?->metadata['is_closed'] ?? false)) {
                $add('closed_year', 'Financial year closed', 1, 'This financial year is read-only.', 'info', route('admin.financial-years.index'));
            }

            return ['items' => $items] + $this->meta($user, $s);
        });
    }

    public function activity(User $user, array $filters): array
    {
        $s = $this->scope($user, $filters);
        $q = DB::table('activity_logs as l')->leftJoin('users as u', 'u.id', '=', 'l.user_id')->leftJoin('shops as sh', 'sh.id', '=', 'l.shop_id')->leftJoin('godowns as g', 'g.id', '=', 'l.godown_id');
        $this->apply($q, $s, 'l');
        if (! $user->isSuperAdmin() && ! $user->can('activity-logs.view')) {
            $q->where('l.user_id', $user->id);
        }
        $items = $q->whereBetween('l.created_at', [$s['from']->startOfDay(), $s['to']->endOfDay()])->latest('l.created_at')->limit(20)->get(['l.id', 'l.event', 'l.action', 'l.module', 'l.auditable_id', 'l.created_at', 'u.name as user', 'sh.name as shop', 'g.name as godown'])->map(fn ($r) => [
            'id' => $r->id, 'user' => $r->user ?? 'System', 'action' => str($r->event ?: $r->action)->replace(['.', '_'], ' ')->title()->toString(), 'module' => str($r->module ?: 'ERP')->headline()->toString(),
            'reference' => $r->auditable_id ? '#'.$r->auditable_id : '—', 'godown' => $r->godown ?? '—', 'shop' => $r->shop ?? '—', 'time' => CarbonImmutable::parse($r->created_at)->diffForHumans(), 'url' => null,
        ])->all();

        return ['items' => $items] + $this->meta($user, $s);
    }

    public function tab(User $user, array $filters, string $tab): array
    {
        $this->authorizeComponent($user, 'tab-'.$tab);
        $s = $this->scope($user, $filters);
        $c = [
            'sales' => ['Sales performance', 'Revenue, margin, products and customers.', ['sales-purchase', 'profit'], ['products', 'customers']],
            'purchases' => ['Purchase performance', 'Purchases, payables and suppliers.', ['sales-purchase', 'payable-ageing'], ['suppliers']],
            'inventory' => ['Inventory health', 'Stock and location comparison.', ['godown-comparison', 'shop-comparison'], []],
            'finance' => ['Financial position', 'Profit, expenses and ageing.', ['profit', 'expenses', 'receivable-ageing', 'payable-ageing'], []],
            'collections' => ['Collection performance', 'Collections and payment methods.', ['collections', 'payment-methods'], ['customers']],
            'deliveries' => ['Delivery operations', 'Pending and failed delivery workload.', [], []],
            'alerts' => ['Alerts and reminders', 'Operational exceptions requiring action.', [], []],
            'activity' => ['Recent activity', 'Auditable work performed in this scope.', [], []],
        ][$tab];

        return ['title' => $c[0], 'description' => $c[1], 'charts' => $c[2], 'tops' => $c[3], 'tab' => $tab] + $this->meta($user, $s);
    }

    private function scope(User $user, array $filters): array
    {
        $shop = session('active_shop_id') ? (int) session('active_shop_id') : null;
        $godown = session('active_godown_id') ? (int) session('active_godown_id') : null;
        $yearId = session('active_financial_year_id') ? (int) session('active_financial_year_id') : null;
        if ($shop) {
            abort_unless($user->canAccessShop($shop), 403, 'You do not have access to the selected shop.');
        }
        if ($godown) {
            abort_unless($user->canAccessGodown($godown), 403, 'You do not have access to the selected godown.');
        }
        $year = $yearId ? ReferenceMaster::ofType('financial_year')->find($yearId) : null;
        if ($yearId && ! $user->isSuperAdmin()) {
            abort_unless($user->financialYears()->wherePivot('is_active', true)->whereKey($yearId)->exists(), 403, 'You do not have access to the selected financial year.');
        }
        $today = CarbonImmutable::today();
        [$from,$to] = match ($filters['preset'] ?? 'this_month') {
            'today' => [$today, $today], 'yesterday' => [$today->subDay(), $today->subDay()],
            'this_week' => [$today->startOfWeek(), $today],
            'last_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
            'this_quarter' => [$today->startOfQuarter(), $today],
            'financial_year' => [CarbonImmutable::parse($year?->metadata['start_date'] ?? $today->startOfYear()), CarbonImmutable::parse($year?->metadata['end_date'] ?? $today)],
            'custom' => [CarbonImmutable::parse($filters['date_from']), CarbonImmutable::parse($filters['date_to'])],
            default => [$today->startOfMonth(), $today]
        };

        return ['user' => $user, 'shop_id' => $shop, 'godown_id' => $godown, 'financial_year_id' => $yearId, 'financial_year' => $year,
            'permitted_shop_ids' => $user->isSuperAdmin() ? null : array_map('intval', session('permitted_shop_ids', [])),
            'mode' => $godown ? 'godown' : (! $shop && $user->isSuperAdmin() ? 'company' : 'shop'),
            'preset' => $filters['preset'] ?? 'this_month', 'from' => $from, 'to' => $to, 'date_from' => $from->toDateString(), 'date_to' => $to->toDateString()];
    }

    private function apply(Builder $q, array $s, string $a, bool $financial = true, bool $godown = true): Builder
    {
        $c = fn ($n) => $a.'.'.$n;
        if ($s['mode'] !== 'godown' && $s['shop_id']) {
            $q->where($c('shop_id'), $s['shop_id']);
        } elseif ($s['permitted_shop_ids'] !== null) {
            $q->whereIn($c('shop_id'), $s['permitted_shop_ids'] ?: [-1]);
        }
        if ($godown && $s['godown_id']) {
            $q->where($c('godown_id'), $s['godown_id']);
        }
        if ($financial) {
            $s['financial_year_id'] ? $q->where($c('financial_year_id'), $s['financial_year_id']) : $q->whereRaw('1=0');
        }

        return $q;
    }

    private function cached(User $u, array $s, string $part, callable $fn): array
    {
        $key = 'dashboard:'.sha1(implode('|', [$u->id, $part, $s['shop_id'], $s['godown_id'], $s['financial_year_id'], $s['date_from'], $s['date_to']]));

        return Cache::remember($key, now()->addSeconds(45), $fn);
    }

    private function totals(array $s, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->documentTotals($s, $from, $to) + $this->paymentTotals($s, $from, $to) + $this->operationTotals($s, $from, $to);
    }

    private function documentTotals(array $s, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $q = DB::table('commercial_documents as d');
        $this->apply($q, $s, 'd');
        $q->where('d.status', 'posted')->whereBetween('d.document_date', $this->range($from, $to));
        $r = $q->selectRaw($this->documentSummarySql())->first();

        return ['sales' => (float) $r->sales, 'purchases' => (float) $r->purchases, 'sales_returns' => (float) $r->sales_returns, 'purchase_returns' => (float) $r->purchase_returns] + $this->balanceTotals($s, $to);
    }

    private function documentSummarySql(): string
    {
        $sales = 'COALESCE(SUM(CASE WHEN d.type IN (\'sales_invoice\',\'pos_invoice\') THEN d.total_amount WHEN d.type=\'sales_return\' THEN -d.total_amount ELSE 0 END),0) sales';
        $purchases = 'COALESCE(SUM(CASE WHEN d.type IN (\'purchase_bill\',\'goods_receipt\') THEN d.total_amount WHEN d.type=\'purchase_return\' THEN -d.total_amount ELSE 0 END),0) purchases';
        $returns = 'COALESCE(SUM(CASE WHEN d.type=\'sales_return\' THEN d.total_amount ELSE 0 END),0) sales_returns,COALESCE(SUM(CASE WHEN d.type=\'purchase_return\' THEN d.total_amount ELSE 0 END),0) purchase_returns';

        return implode(',', [$sales, $purchases, $returns]);
    }

    private function balanceTotals(array $s, CarbonImmutable $to): array
    {
        $q = DB::table('commercial_documents as d');
        $this->apply($q, $s, 'd');
        $sales = 'COALESCE(SUM(CASE WHEN d.type IN (\'sales_invoice\',\'pos_invoice\') THEN d.balance_amount ELSE 0 END),0) receivable';
        $buy = 'COALESCE(SUM(CASE WHEN d.type IN (\'purchase_bill\',\'goods_receipt\') THEN d.balance_amount ELSE 0 END),0) payable';
        $r = $q->where('d.status', 'posted')->whereDate('d.document_date', '<=', $to)->selectRaw($sales.','.$buy)->first();

        return ['receivable' => (float) $r->receivable, 'payable' => (float) $r->payable];
    }

    private function operationTotals(array $s, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $t = DB::table('stock_transfers as t');
        $this->apply($t, $s, 't', true, false);
        if ($s['godown_id']) {
            $t->where(fn ($q) => $q->where('t.from_godown_id', $s['godown_id'])->orWhere('t.to_godown_id', $s['godown_id']));
        }
        $d = DB::table('deliveries as x')->join('commercial_documents as d', 'd.id', '=', 'x.commercial_document_id');
        $this->apply($d, $s, 'd');

        return ['transfers' => $t->whereBetween('t.transfer_date', $this->range($from, $to))->whereNotIn('t.status', ['received', 'rejected', 'cancelled'])->count(),
            'deliveries' => $d->whereBetween('d.document_date', $this->range($from, $to))->whereNotIn('x.status', ['delivered', 'failed', 'cancelled'])->count()];
    }

    private function paymentTotals(array $s, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $q = DB::table('payments as p')->leftJoin('reference_masters as m', 'm.id', '=', 'p.payment_method_id');
        $this->apply($q, $s, 'p');
        $a = 'COALESCE(SUM(CASE WHEN p.type=\'customer_collection\' THEN p.amount ELSE 0 END),0) collections,COALESCE(SUM(CASE WHEN p.type=\'expense\' THEN p.amount ELSE 0 END),0) expenses';
        $cash = 'COALESCE(SUM(CASE WHEN LOWER(COALESCE(m.name,m.code,\'\')) LIKE \'%cash%\' AND p.type IN (\'cash_receipt\',\'customer_collection\',\'income\') THEN p.amount WHEN LOWER(COALESCE(m.name,m.code,\'\')) LIKE \'%cash%\' AND p.type IN (\'cash_payment\',\'supplier_payment\',\'expense\') THEN -p.amount ELSE 0 END),0) cash';
        $bank = 'COALESCE(SUM(CASE WHEN LOWER(COALESCE(m.name,m.code,\'\')) NOT LIKE \'%cash%\' AND p.type IN (\'bank_receipt\',\'customer_collection\',\'income\') THEN p.amount WHEN LOWER(COALESCE(m.name,m.code,\'\')) NOT LIKE \'%cash%\' AND p.type IN (\'bank_payment\',\'supplier_payment\',\'expense\') THEN -p.amount ELSE 0 END),0) bank';
        $r = $q->whereBetween('p.payment_date', $this->range($from, $to))->selectRaw(implode(',', [$a, $cash, $bank]))->first();

        return ['collections' => (float) $r->collections, 'expenses' => (float) $r->expenses, 'cash' => (float) $r->cash, 'bank' => (float) $r->bank];
    }

    private function productCost(array $s, CarbonImmutable $from, CarbonImmutable $to): float
    {
        $q = DB::table('commercial_document_items as i')->join('commercial_documents as d', 'd.id', '=', 'i.commercial_document_id')->join('products as p', 'p.id', '=', 'i.product_id');
        $this->apply($q, $s, 'd');
        $sql = 'COALESCE(SUM(CASE WHEN d.type=\'sales_return\' THEN -(i.quantity*p.purchase_price) ELSE i.quantity*p.purchase_price END),0) value';

        return (float) $q->where('d.status', 'posted')->whereIn('d.type', array_merge(self::SALES, ['sales_return']))->whereBetween('d.document_date', $this->range($from, $to))->selectRaw($sql)->value('value');
    }

    private function stockSummary(array $s): array
    {
        $q = DB::table('inventory_balances as b')->join('products as p', 'p.id', '=', 'b.product_id');
        $this->apply($q, $s, 'b', false);
        $rows = $q->groupBy('p.id', 'p.reorder_level')->selectRaw('SUM(b.quantity) quantity,SUM(b.quantity*b.average_cost) value,p.reorder_level')->get();

        return ['value' => (float) $rows->sum('value'), 'low' => $rows->filter(fn ($r) => (float) $r->quantity <= (float) $r->reorder_level)->count(), 'out' => $rows->filter(fn ($r) => (float) $r->quantity <= 0)->count()];
    }

    private function documentTrend(array $s): array
    {
        $q = DB::table('commercial_documents as d');
        $this->apply($q, $s, 'd');
        $sales = 'SUM(CASE WHEN d.type IN (\'sales_invoice\',\'pos_invoice\') THEN d.total_amount WHEN d.type=\'sales_return\' THEN -d.total_amount ELSE 0 END) sales';
        $buy = 'SUM(CASE WHEN d.type IN (\'purchase_bill\',\'goods_receipt\') THEN d.total_amount WHEN d.type=\'purchase_return\' THEN -d.total_amount ELSE 0 END) purchases';
        $r = $q->where('d.status', 'posted')->whereBetween('d.document_date', [$s['date_from'], $s['date_to']])->groupBy('d.document_date')->orderBy('d.document_date')->selectRaw('d.document_date label,'.$sales.','.$buy)->get();

        return ['type' => 'area', 'labels' => $r->pluck('label')->all(), 'series' => [['name' => 'Sales', 'data' => $r->pluck('sales')->map(fn ($v) => (float) $v)->all()], ['name' => 'Purchases', 'data' => $r->pluck('purchases')->map(fn ($v) => (float) $v)->all()]]];
    }

    private function paymentTrend(array $s, string $type, string $label): array
    {
        $q = DB::table('payments as p');
        $this->apply($q, $s, 'p');
        $r = $q->where('p.type', $type)->whereBetween('p.payment_date', [$s['date_from'], $s['date_to']])->groupBy('p.payment_date')->orderBy('p.payment_date')->selectRaw('p.payment_date label,SUM(p.amount) value')->get();

        return ['type' => 'area', 'labels' => $r->pluck('label')->all(), 'series' => [['name' => $label, 'data' => $r->pluck('value')->map(fn ($v) => (float) $v)->all()]]];
    }

    private function profitTrend(array $s): array
    {
        $trend = $this->documentTrend($s);
        $q = DB::table('payments as p');
        $this->apply($q, $s, 'p');
        $expenses = $q->where('p.type', 'expense')->whereBetween('p.payment_date', [$s['date_from'], $s['date_to']])->groupBy('p.payment_date')->pluck(DB::raw('SUM(p.amount)'), 'p.payment_date');
        $cost = $this->productCostTrend($s);
        $sales = $trend['series'][0]['data'];
        $gross = collect($trend['labels'])->map(fn ($date, $i) => $sales[$i] - (float) ($cost[$date] ?? 0))->all();
        $trend['series'] = [['name' => 'Gross Profit (estimated)', 'data' => $gross], ['name' => 'Net Profit (estimated)', 'data' => collect($trend['labels'])->map(fn ($date, $i) => $gross[$i] - (float) ($expenses[$date] ?? 0))->all()]];

        return $trend;
    }

    private function productCostTrend(array $s)
    {
        $q = DB::table('commercial_document_items as i')->join('commercial_documents as d', 'd.id', '=', 'i.commercial_document_id')->join('products as p', 'p.id', '=', 'i.product_id');
        $this->apply($q, $s, 'd');
        $sql = 'SUM(CASE WHEN d.type=\'sales_return\' THEN -(i.quantity*p.purchase_price) ELSE i.quantity*p.purchase_price END) value';

        return $q->where('d.status', 'posted')->whereIn('d.type', array_merge(self::SALES, ['sales_return']))->whereBetween('d.document_date', [$s['date_from'], $s['date_to']])->groupBy('d.document_date')->pluck(DB::raw($sql), 'd.document_date');
    }

    private function expenseCategories(array $s): array
    {
        $q = DB::table('payments as p')->leftJoin('reference_masters as m', 'm.id', '=', 'p.payment_method_id');
        $this->apply($q, $s, 'p');
        $r = $q->where('p.type', 'expense')->whereBetween('p.payment_date', [$s['date_from'], $s['date_to']])->groupBy('m.id', 'm.name')->selectRaw('COALESCE(m.name,\'Uncategorised\') label,SUM(p.amount) value')->orderByDesc('value')->get();

        return ['type' => 'donut', 'labels' => $r->pluck('label')->all(), 'series' => $r->pluck('value')->map(fn ($v) => (float) $v)->all()];
    }

    private function locationComparison(array $s, string $kind): array
    {
        $q = DB::table('commercial_documents as d');
        $this->apply($q, $s, 'd');
        $kind === 'shop' ? $q->join('shops as l', 'l.id', '=', 'd.shop_id') : $q->leftJoin('godowns as l', 'l.id', '=', 'd.godown_id');
        $r = $q->where('d.status', 'posted')->whereIn('d.type', self::SALES)->whereBetween('d.document_date', [$s['date_from'], $s['date_to']])->groupBy('l.id', 'l.name')->selectRaw('COALESCE(l.name,\'Unassigned\') label,SUM(d.total_amount) value')->orderByDesc('value')->limit(12)->get();

        return ['type' => 'bar', 'labels' => $r->pluck('label')->all(), 'series' => [['name' => 'Sales', 'data' => $r->pluck('value')->map(fn ($v) => (float) $v)->all()]]];
    }

    private function paymentMethods(array $s): array
    {
        $q = DB::table('payments as p')->leftJoin('reference_masters as m', 'm.id', '=', 'p.payment_method_id');
        $this->apply($q, $s, 'p');
        $r = $q->whereBetween('p.payment_date', [$s['date_from'], $s['date_to']])->groupBy('m.id', 'm.name')->selectRaw('COALESCE(m.name,\'Unspecified\') label,SUM(p.amount) value')->orderByDesc('value')->get();

        return ['type' => 'donut', 'labels' => $r->pluck('label')->all(), 'series' => $r->pluck('value')->map(fn ($v) => (float) $v)->all()];
    }

    private function ageing(array $s, array $types, string $label): array
    {
        $q = DB::table('commercial_documents as d');
        $this->apply($q, $s, 'd');
        $date = CarbonImmutable::parse($s['date_to']);
        $due = 'COALESCE(d.due_date,d.document_date)';
        $sql = 'SUM(CASE WHEN '.$due.'>=? THEN d.balance_amount ELSE 0 END) current,SUM(CASE WHEN '.$due.' BETWEEN ? AND ? THEN d.balance_amount ELSE 0 END) d30,SUM(CASE WHEN '.$due.' BETWEEN ? AND ? THEN d.balance_amount ELSE 0 END) d60,SUM(CASE WHEN '.$due.' BETWEEN ? AND ? THEN d.balance_amount ELSE 0 END) d90,SUM(CASE WHEN '.$due.'<? THEN d.balance_amount ELSE 0 END) over90';
        $bind = [$date->toDateString(), $date->subDays(30)->toDateString(), $date->subDay()->toDateString(), $date->subDays(60)->toDateString(), $date->subDays(31)->toDateString(), $date->subDays(90)->toDateString(), $date->subDays(61)->toDateString(), $date->subDays(90)->toDateString()];
        $r = $q->where('d.status', 'posted')->whereIn('d.type', $types)->where('d.balance_amount', '>', 0)->whereDate('d.document_date', '<=', $date)->selectRaw($sql, $bind)->first();

        return ['type' => 'bar', 'labels' => ['Current', '1–30', '31–60', '61–90', '90+'], 'series' => [['name' => $label, 'data' => [(float) $r->current, (float) $r->d30, (float) $r->d60, (float) $r->d90, (float) $r->over90]]]];
    }

    private function topProducts(array $s): array
    {
        $q = DB::table('commercial_document_items as i')->join('commercial_documents as d', 'd.id', '=', 'i.commercial_document_id')->join('products as p', 'p.id', '=', 'i.product_id');
        $this->apply($q, $s, 'd');
        $sql = 'p.name,p.sku,SUM(CASE WHEN d.type=\'sales_return\' THEN -i.quantity ELSE i.quantity END) quantity,SUM(CASE WHEN d.type=\'sales_return\' THEN -i.line_total ELSE i.line_total END) value';

        return $q->where('d.status', 'posted')->whereIn('d.type', array_merge(self::SALES, ['sales_return']))->whereBetween('d.document_date', [$s['date_from'], $s['date_to']])->groupBy('p.id', 'p.name', 'p.sku')->selectRaw($sql)->orderByDesc('value')->limit(10)->get()->map(fn ($r) => ['name' => $r->name, 'code' => $r->sku, 'quantity' => (float) $r->quantity, 'value' => (float) $r->value])->all();
    }

    private function topParties(array $s, string $type): array
    {
        $party = $type === 'customers' ? ['customer', 'both'] : ['supplier', 'both'];
        $docs = $type === 'customers' ? self::SALES : self::PURCHASES;
        $q = DB::table('commercial_documents as d')->join('parties as p', 'p.id', '=', 'd.party_id');
        $this->apply($q, $s, 'd');

        return $q->where('d.status', 'posted')->whereIn('p.type', $party)->whereIn('d.type', $docs)->whereBetween('d.document_date', [$s['date_from'], $s['date_to']])->groupBy('p.id', 'p.name', 'p.code')->selectRaw('p.name,p.code,COUNT(d.id) quantity,SUM(d.total_amount) value')->orderByDesc('value')->limit(10)->get()->map(fn ($r) => ['name' => $r->name, 'code' => $r->code, 'quantity' => (int) $r->quantity, 'value' => (float) $r->value])->all();
    }

    private function alertCounts(array $s): array
    {
        $q = DB::table('commercial_documents as d');
        $this->apply($q, $s, 'd');
        $sql = 'SUM(CASE WHEN d.type IN (\'sales_invoice\',\'pos_invoice\') AND d.due_date<? THEN 1 ELSE 0 END) overdue_receivable,SUM(CASE WHEN d.type IN (\'purchase_bill\',\'goods_receipt\') AND d.due_date<=? THEN 1 ELSE 0 END) supplier_due';
        $d = $q->where('d.status', 'posted')->where('d.balance_amount', '>', 0)->selectRaw($sql, [now()->toDateString(), now()->addDays(7)->toDateString()])->first();
        $q = DB::table('stock_transfers as t');
        $this->apply($q, $s, 't', true, false);
        if ($s['godown_id']) {
            $q->where(fn ($query) => $query->where('t.from_godown_id', $s['godown_id'])->orWhere('t.to_godown_id', $s['godown_id']));
        }
        $t = $q->selectRaw('SUM(CASE WHEN t.status IN (\'requested\',\'draft\') THEN 1 ELSE 0 END) transfer_approval,SUM(CASE WHEN t.status IN (\'approved\',\'dispatched\',\'in_transit\',\'partially_received\') THEN 1 ELSE 0 END) transfer_receipt')->first();

        return ['overdue_receivable' => (int) $d->overdue_receivable, 'supplier_due' => (int) $d->supplier_due, 'transfer_approval' => (int) $t->transfer_approval, 'transfer_receipt' => (int) $t->transfer_receipt] + $this->deliveryAlertCounts($s);
    }

    private function deliveryAlertCounts(array $s): array
    {
        $q = DB::table('deliveries as x')->join('commercial_documents as d', 'd.id', '=', 'x.commercial_document_id');
        $this->apply($q, $s, 'd');
        $r = $q->selectRaw('SUM(CASE WHEN x.status IN (\'pending\',\'assigned\',\'out_for_delivery\',\'rescheduled\') THEN 1 ELSE 0 END) pending_delivery,SUM(CASE WHEN x.status=\'failed\' THEN 1 ELSE 0 END) failed_delivery')->first();

        return ['pending_delivery' => (int) $r->pending_delivery, 'failed_delivery' => (int) $r->failed_delivery];
    }

    private function range(CarbonImmutable $from,CarbonImmutable $to): array
    {
        return [$from->toDateString(), $to->toDateString()];
    }

    private function authorizeComponent(User $user,string $component): void
    {
        $allowed = match ($component) {
            'sales-purchase' => $user->can('reports.view') || ($user->can('sales-invoices.view') && $user->can('purchase-bills.view')),
            'profit','shop-comparison','godown-comparison' => $user->can('reports.view'),
            'collections','payment-methods','expenses' => $user->can('payments.view') || $user->can('accounts.view') || $user->can('reports.view'),
            'receivable-ageing','payable-ageing' => $user->can('accounts.view') || $user->can('reports.view'),
            'top-products','top-customers' => $user->can('sales-invoices.view') || $user->can('reports.view'),
            'top-suppliers' => $user->can('purchase-bills.view') || $user->can('reports.view'),
            'tab-sales' => $user->can('sales-invoices.view') || $user->can('reports.view'),
            'tab-purchases' => $user->can('purchase-bills.view') || $user->can('reports.view'),
            'tab-inventory' => $user->can('stock.view') || $user->can('reports.view'),
            'tab-finance' => $user->can('accounts.view') || $user->can('reports.view'),
            'tab-collections' => $user->can('payments.view') || $user->can('reports.view'),
            'tab-deliveries' => $user->can('deliveries.view'),
            default => true,
        };
        abort_unless($allowed,403,'You do not have permission to view this dashboard component.');
    }
}

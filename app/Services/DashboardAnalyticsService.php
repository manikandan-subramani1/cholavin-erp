<?php

namespace App\Services;

use App\Models\ContactEnquiry;
use App\Models\Godown;
use App\Models\Product;
use App\Models\ReferenceMaster;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    private const SALES_TYPES = ['sales_invoice', 'pos_invoice', 'sales_return'];

    private const PURCHASE_TYPES = ['purchase_bill', 'goods_receipt', 'purchase_return'];

    public function build(User $user, int $days): array
    {
        $shopId = (int) session('active_shop_id');
        $godownId = (int) session('active_godown_id');
        $to = CarbonImmutable::today();
        $from = $to->subDays($days - 1);
        $previousTo = $from->subDay();
        $previousFrom = $previousTo->subDays($days - 1);
        $visibility = $this->visibility($user);

        $currentDocuments = $this->documentSummary($shopId, $godownId, $from, $to);
        $previousDocuments = $this->documentSummary($shopId, $godownId, $previousFrom, $previousTo);
        $this->removeRestrictedDocumentValues($currentDocuments, $visibility);
        $this->removeRestrictedDocumentValues($previousDocuments, $visibility);
        $currentProductCost = $visibility['sales'] ? $this->estimatedProductCost($shopId, $godownId, $from, $to) : 0;
        $previousProductCost = $visibility['sales'] ? $this->estimatedProductCost($shopId, $godownId, $previousFrom, $previousTo) : 0;
        $currentExpenses = $visibility['expenses'] ? $this->operatingExpenses($shopId, $godownId, $from, $to) : 0;
        $previousExpenses = $visibility['expenses'] ? $this->operatingExpenses($shopId, $godownId, $previousFrom, $previousTo) : 0;
        $stock = $visibility['stock'] ? $this->stockAnalytics($shopId, $godownId) : $this->emptyStockAnalytics();

        $grossProfit = $currentDocuments['sales'] - $currentProductCost;
        $previousGrossProfit = $previousDocuments['sales'] - $previousProductCost;
        $netContribution = $grossProfit - $currentExpenses;
        $previousNetContribution = $previousGrossProfit - $previousExpenses;

        return [
            'period' => [
                'days' => $days,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'label' => $days === 365 ? 'Last 12 months' : "Last {$days} days",
            ],
            'visibility' => $visibility,
            'metrics' => $this->moduleMetrics($shopId, $godownId),
            'masterChecks' => $this->masterChecks($shopId),
            'kpis' => $this->kpis(
                $visibility,
                $currentDocuments,
                $previousDocuments,
                $currentProductCost,
                $previousProductCost,
                $currentExpenses,
                $previousExpenses,
                $grossProfit,
                $previousGrossProfit,
                $netContribution,
                $previousNetContribution,
                $stock,
                $shopId,
                $godownId,
            ),
            'costBreakdown' => $this->costBreakdown($visibility, $currentDocuments, $currentProductCost, $currentExpenses),
            'trend' => $this->financialTrend($shopId, $godownId, $from, $to, $days, $visibility),
            'topProducts' => $visibility['sales'] ? $this->topSellingProducts($shopId, $godownId, $from, $to) : collect(),
            'stock' => $stock,
        ];
    }

    private function visibility(User $user): array
    {
        return [
            'sales' => $user->can('sales-invoices.view') || $user->can('reports.view'),
            'purchases' => $user->can('purchase-bills.view') || $user->can('reports.view'),
            'stock' => $user->can('stock.view') || $user->can('reports.view'),
            'accounts' => $user->can('accounts.view') || $user->can('reports.view'),
            'expenses' => $user->can('payments.view') || $user->can('accounts.view') || $user->can('reports.view'),
            'reports' => $user->can('reports.view'),
        ];
    }

    private function removeRestrictedDocumentValues(array &$summary, array $visibility): void
    {
        if (! $visibility['sales']) {
            $summary['sales'] = 0;
            $summary['sales_discount'] = 0;
            $summary['sales_tax'] = 0;
        }

        if (! $visibility['purchases']) {
            $summary['purchases'] = 0;
            $summary['purchase_tax'] = 0;
            $summary['purchase_expense'] = 0;
        }
    }

    private function costBreakdown(array $visibility, array $documents, float $productCost, float $expenses): array
    {
        $items = [];
        if ($visibility['sales']) {
            $items[] = ['label' => 'Estimated product cost', 'value' => $productCost, 'icon' => 'ri-box-3-line', 'tone' => 'maroon'];
            $items[] = ['label' => 'Sales discounts', 'value' => $documents['sales_discount'], 'icon' => 'ri-coupon-3-line', 'tone' => 'gold'];
            $items[] = ['label' => 'Output tax', 'value' => $documents['sales_tax'], 'icon' => 'ri-percent-line', 'tone' => 'green'];
        }
        if ($visibility['purchases']) {
            $items[] = ['label' => 'Input tax', 'value' => $documents['purchase_tax'], 'icon' => 'ri-refund-2-line', 'tone' => 'blue'];
            $items[] = ['label' => 'Purchase charges', 'value' => $documents['purchase_expense'], 'icon' => 'ri-truck-line', 'tone' => 'purple'];
        }
        if ($visibility['expenses']) {
            $items[] = ['label' => 'Operating expenses', 'value' => $expenses, 'icon' => 'ri-wallet-3-line', 'tone' => 'red'];
        }

        return $items;
    }

    private function moduleMetrics(int $shopId, int $godownId): array
    {
        return [
            ['label' => 'Products', 'value' => Product::query()->where('is_active', true)->count(), 'icon' => 'ri-shopping-bag-3-line', 'route' => 'admin.products.index', 'ability' => ['viewAny', Product::class]],
            ['label' => 'Open Enquiries', 'value' => ContactEnquiry::query()->where('status', 'not_contacted')->count(), 'icon' => 'ri-customer-service-2-line', 'route' => 'admin.enquiries.index', 'ability' => ['viewAny', ContactEnquiry::class]],
            ['label' => 'Active Users', 'value' => User::query()->where('is_active', true)->count(), 'icon' => 'ri-team-line', 'route' => 'admin.users.index', 'ability' => 'users.view'],
            ['label' => 'Active Locations', 'value' => Shop::query()->where('is_active', true)->count() + Godown::query()->where('is_active', true)->count(), 'icon' => 'ri-store-2-line', 'route' => 'admin.locations.index', 'ability' => 'shops.view'],
            ['label' => 'Customers', 'value' => DB::table('parties')->where('shop_id', $shopId)->whereIn('type', ['customer', 'both'])->count(), 'icon' => 'ri-user-smile-line', 'route' => 'admin.parties.index', 'route_parameters' => ['customers'], 'ability' => 'customers.view'],
            ['label' => 'Sales Invoices', 'value' => DB::table('commercial_documents')->where('shop_id', $shopId)->when($godownId, fn (Builder $query) => $query->where('godown_id', $godownId))->where('type', 'sales_invoice')->count(), 'icon' => 'ri-file-list-3-line', 'route' => 'admin.documents.index', 'route_parameters' => ['sales-invoices'], 'ability' => 'sales-invoices.view'],
            ['label' => 'Stock Items', 'value' => DB::table('inventory_balances')->where('shop_id', $shopId)->when($godownId, fn (Builder $query) => $query->where('godown_id', $godownId))->where('quantity', '>', 0)->distinct()->count('product_id'), 'icon' => 'ri-stack-line', 'route' => 'admin.stock.index', 'ability' => 'stock.view'],
        ];
    }

    private function masterChecks(int $shopId): array
    {
        $settings = Setting::values();

        return [
            ['label' => 'Products', 'count' => Product::query()->count(), 'route' => 'admin.products.create', 'ability' => ['create', Product::class], 'message' => 'Add the first product to make billing ready.'],
            ['label' => 'Shops', 'count' => Shop::query()->count(), 'route' => 'admin.locations.index', 'ability' => 'shops.create', 'message' => 'Create a shop before entering shop-owned transactions.'],
            ['label' => 'Godowns', 'count' => Godown::query()->count(), 'route' => 'admin.locations.index', 'ability' => 'godowns.create', 'message' => 'Add a godown for inventory movement and stock.'],
            ['label' => 'Staff users', 'count' => User::query()->whereHas('role', fn ($query) => $query->where('is_super_admin', false))->count(), 'route' => 'admin.users.index', 'ability' => 'users.create', 'message' => 'Create staff and assign their working locations.'],
            ['label' => 'Company contact', 'count' => collect(['contact_phone', 'contact_email', 'company_address'])->filter(fn ($key) => filled($settings[$key] ?? null))->count(), 'expected' => 3, 'route' => 'admin.settings.index', 'ability' => 'settings.update', 'message' => 'Complete phone, email, and company address.'],
            ['label' => 'Units', 'count' => ReferenceMaster::ofType('unit')->where('is_active', true)->count(), 'route' => 'admin.masters.index', 'route_parameters' => ['units'], 'ability' => 'units.create', 'message' => 'Create units before adding inventory products.'],
            ['label' => 'Customers', 'count' => DB::table('parties')->where('shop_id', $shopId)->whereIn('type', ['customer', 'both'])->count(), 'route' => 'admin.parties.index', 'route_parameters' => ['customers'], 'ability' => 'customers.create', 'message' => 'Add a customer before creating sales documents.'],
            ['label' => 'Suppliers', 'count' => DB::table('parties')->where('shop_id', $shopId)->whereIn('type', ['supplier', 'both'])->count(), 'route' => 'admin.parties.index', 'route_parameters' => ['suppliers'], 'ability' => 'suppliers.create', 'message' => 'Add a supplier before creating purchase documents.'],
        ];
    }

    private function documentSummary(int $shopId, int $godownId, CarbonImmutable $from, CarbonImmutable $to): array
    {
        if (! $shopId) {
            return $this->emptyDocumentSummary();
        }

        $row = DB::table('commercial_documents')
            ->where('shop_id', $shopId)
            ->when($godownId, fn (Builder $query) => $query->where('godown_id', $godownId))
            ->where('status', 'posted')
            ->whereDate('document_date', '>=', $from->toDateString())
            ->whereDate('document_date', '<=', $to->toDateString())
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('sales_invoice','pos_invoice') THEN subtotal - discount_amount WHEN type = 'sales_return' THEN -(subtotal - discount_amount) ELSE 0 END), 0) as sales")
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('purchase_bill','goods_receipt') THEN subtotal - discount_amount WHEN type = 'purchase_return' THEN -(subtotal - discount_amount) ELSE 0 END), 0) as purchases")
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('sales_invoice','pos_invoice') THEN discount_amount WHEN type = 'sales_return' THEN -discount_amount ELSE 0 END), 0) as sales_discount")
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('sales_invoice','pos_invoice') THEN tax_amount WHEN type = 'sales_return' THEN -tax_amount ELSE 0 END), 0) as sales_tax")
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('purchase_bill','goods_receipt') THEN tax_amount WHEN type = 'purchase_return' THEN -tax_amount ELSE 0 END), 0) as purchase_tax")
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('purchase_bill','goods_receipt') THEN expense_amount WHEN type = 'purchase_return' THEN -expense_amount ELSE 0 END), 0) as purchase_expense")
            ->first();

        return collect($this->emptyDocumentSummary())
            ->mapWithKeys(fn ($value, $key) => [$key => (float) ($row->{$key} ?? 0)])
            ->all();
    }

    private function emptyDocumentSummary(): array
    {
        return ['sales' => 0, 'purchases' => 0, 'sales_discount' => 0, 'sales_tax' => 0, 'purchase_tax' => 0, 'purchase_expense' => 0];
    }

    private function estimatedProductCost(int $shopId, int $godownId, CarbonImmutable $from, CarbonImmutable $to): float
    {
        if (! $shopId) {
            return 0;
        }

        return (float) DB::table('commercial_document_items as items')
            ->join('commercial_documents as documents', 'documents.id', '=', 'items.commercial_document_id')
            ->join('products', 'products.id', '=', 'items.product_id')
            ->where('documents.shop_id', $shopId)
            ->when($godownId, fn (Builder $query) => $query->where('documents.godown_id', $godownId))
            ->where('documents.status', 'posted')
            ->whereIn('documents.type', self::SALES_TYPES)
            ->whereDate('documents.document_date', '>=', $from->toDateString())
            ->whereDate('documents.document_date', '<=', $to->toDateString())
            ->selectRaw("COALESCE(SUM(CASE WHEN documents.type = 'sales_return' THEN -(items.quantity * products.purchase_price) ELSE items.quantity * products.purchase_price END), 0) as estimated_cost")
            ->value('estimated_cost');
    }

    private function operatingExpenses(int $shopId, int $godownId, CarbonImmutable $from, CarbonImmutable $to): float
    {
        if (! $shopId) {
            return 0;
        }

        return (float) DB::table('payments')
            ->where('shop_id', $shopId)
            ->when($godownId, fn (Builder $query) => $query->where(fn (Builder $query) => $query->whereNull('godown_id')->orWhere('godown_id', $godownId)))
            ->where('type', 'expense')
            ->whereDate('payment_date', '>=', $from->toDateString())
            ->whereDate('payment_date', '<=', $to->toDateString())
            ->sum('amount');
    }

    private function kpis(
        array $visibility,
        array $current,
        array $previous,
        float $currentCost,
        float $previousCost,
        float $currentExpenses,
        float $previousExpenses,
        float $grossProfit,
        float $previousGrossProfit,
        float $netContribution,
        float $previousNetContribution,
        array $stock,
        int $shopId,
        int $godownId,
    ): array {
        $cards = [];
        if ($visibility['sales']) {
            $cards[] = $this->kpi('Net Sales', $current['sales'], 'ri-line-chart-line', 'green', 'admin.documents.index', ['sales-invoices'], $this->change($current['sales'], $previous['sales']));
            $cards[] = $this->kpi(
                'Estimated Gross Profit',
                $grossProfit,
                'ri-funds-line',
                $grossProfit >= 0 ? 'gold' : 'red',
                $visibility['reports'] ? 'admin.reports.index' : 'admin.documents.index',
                [$visibility['reports'] ? 'profit-loss' : 'sales-invoices'],
                $this->change($grossProfit, $previousGrossProfit),
            );
        }
        if ($visibility['purchases']) {
            $cards[] = $this->kpi('Purchases', $current['purchases'], 'ri-shopping-cart-2-line', 'blue', 'admin.documents.index', ['purchase-bills'], $this->change($current['purchases'], $previous['purchases']));
        }
        if ($visibility['sales'] && $visibility['expenses']) {
            $cards[] = $this->kpi(
                'Net Contribution',
                $netContribution,
                'ri-pie-chart-2-line',
                $netContribution >= 0 ? 'maroon' : 'red',
                $visibility['reports'] ? 'admin.reports.index' : 'admin.payments.index',
                $visibility['reports'] ? ['profit-loss'] : ['type' => 'expense'],
                $this->change($netContribution, $previousNetContribution),
            );
        }
        if ($visibility['expenses']) {
            $cards[] = $this->kpi('Operating Expenses', $currentExpenses, 'ri-wallet-3-line', 'red', 'admin.payments.index', ['type' => 'expense'], $this->change($currentExpenses, $previousExpenses), inverse: true);
        }
        if ($visibility['stock']) {
            $cards[] = $this->kpi('Stock Cost Value', $stock['summary']['cost_value'], 'ri-stack-line', 'purple', 'admin.stock.index');
            $cards[] = $this->kpi(
                'Potential Stock Margin',
                $stock['summary']['potential_margin'],
                'ri-scales-3-line',
                'gold',
                $visibility['reports'] ? 'admin.reports.index' : 'admin.stock.index',
                $visibility['reports'] ? ['stock-valuation'] : [],
            );
        }
        if ($visibility['accounts']) {
            $cards[] = $this->kpi('Receivables', $this->outstanding($shopId, $godownId, ['sales_invoice', 'pos_invoice']), 'ri-hand-coin-line', 'orange', $visibility['reports'] ? 'admin.reports.index' : 'admin.vouchers.index', $visibility['reports'] ? ['receivables'] : []);
            $cards[] = $this->kpi('Payables', $this->outstanding($shopId, $godownId, ['purchase_bill']), 'ri-refund-2-line', 'blue', $visibility['reports'] ? 'admin.reports.index' : 'admin.vouchers.index', $visibility['reports'] ? ['payables'] : []);
        }

        return $cards;
    }

    private function kpi(string $label, float $value, string $icon, string $tone, string $route, array $parameters = [], ?float $change = null, bool $inverse = false): array
    {
        return compact('label', 'value', 'icon', 'tone', 'route', 'parameters', 'change', 'inverse');
    }

    private function change(float $current, float $previous): ?float
    {
        if (abs($previous) < 0.01) {
            return null;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    private function outstanding(int $shopId, int $godownId, array $types): float
    {
        if (! $shopId) {
            return 0;
        }

        return (float) DB::table('commercial_documents')
            ->where('shop_id', $shopId)
            ->when($godownId, fn (Builder $query) => $query->where('godown_id', $godownId))
            ->where('status', 'posted')
            ->whereIn('type', $types)
            ->where('balance_amount', '>', 0)
            ->sum('balance_amount');
    }

    private function financialTrend(int $shopId, int $godownId, CarbonImmutable $from, CarbonImmutable $to, int $days, array $visibility): array
    {
        $buckets = $this->trendBuckets($from, $to, $days);
        $documents = collect();
        $expenses = collect();

        if ($shopId && ($visibility['sales'] || $visibility['purchases'])) {
            $documents = DB::table('commercial_documents')
                ->where('shop_id', $shopId)
                ->when($godownId, fn (Builder $query) => $query->where('godown_id', $godownId))
                ->where('status', 'posted')
                ->whereIn('type', array_merge(self::SALES_TYPES, self::PURCHASE_TYPES))
                ->whereDate('document_date', '>=', $from->toDateString())
                ->whereDate('document_date', '<=', $to->toDateString())
                ->selectRaw('document_date, type, SUM(subtotal - discount_amount) as total')
                ->groupBy('document_date', 'type')
                ->get();
        }

        if ($shopId && $visibility['expenses']) {
            $expenses = DB::table('payments')
                ->where('shop_id', $shopId)
                ->when($godownId, fn (Builder $query) => $query->where(fn (Builder $query) => $query->whereNull('godown_id')->orWhere('godown_id', $godownId)))
                ->where('type', 'expense')
                ->whereDate('payment_date', '>=', $from->toDateString())
                ->whereDate('payment_date', '<=', $to->toDateString())
                ->selectRaw('payment_date, SUM(amount) as total')
                ->groupBy('payment_date')
                ->get();
        }

        foreach ($documents as $row) {
            $key = $this->trendKey(CarbonImmutable::parse($row->document_date), $days);
            if (! isset($buckets[$key])) {
                continue;
            }
            $total = (float) $row->total;
            if (in_array($row->type, ['sales_invoice', 'pos_invoice'], true)) {
                $buckets[$key]['sales'] += $total;
            } elseif ($row->type === 'sales_return') {
                $buckets[$key]['sales'] -= $total;
            } elseif (in_array($row->type, ['purchase_bill', 'goods_receipt'], true)) {
                $buckets[$key]['purchases'] += $total;
            } elseif ($row->type === 'purchase_return') {
                $buckets[$key]['purchases'] -= $total;
            }
        }

        foreach ($expenses as $row) {
            $key = $this->trendKey(CarbonImmutable::parse($row->payment_date), $days);
            if (isset($buckets[$key])) {
                $buckets[$key]['expenses'] += (float) $row->total;
            }
        }

        $series = [];
        if ($visibility['sales']) {
            $series[] = ['name' => 'Net Sales', 'data' => array_values(array_map(fn ($bucket) => round($bucket['sales'], 2), $buckets))];
        }
        if ($visibility['purchases']) {
            $series[] = ['name' => 'Purchases', 'data' => array_values(array_map(fn ($bucket) => round($bucket['purchases'], 2), $buckets))];
        }
        if ($visibility['expenses']) {
            $series[] = ['name' => 'Operating Expenses', 'data' => array_values(array_map(fn ($bucket) => round($bucket['expenses'], 2), $buckets))];
        }

        return [
            'labels' => array_values(array_column($buckets, 'label')),
            'series' => $series,
        ];
    }

    private function trendBuckets(CarbonImmutable $from, CarbonImmutable $to, int $days): array
    {
        $buckets = [];
        $cursor = $days > 90 ? $from->startOfMonth() : $from;
        while ($cursor <= $to) {
            $key = $this->trendKey($cursor, $days);
            $buckets[$key] = [
                'label' => $days > 90 ? $cursor->format('M Y') : $cursor->format('d M'),
                'sales' => 0,
                'purchases' => 0,
                'expenses' => 0,
            ];
            $cursor = $days > 90 ? $cursor->addMonth() : $cursor->addDay();
        }

        return $buckets;
    }

    private function trendKey(CarbonImmutable $date, int $days): string
    {
        return $days > 90 ? $date->format('Y-m') : $date->toDateString();
    }

    private function topSellingProducts(int $shopId, int $godownId, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        if (! $shopId) {
            return collect();
        }

        return DB::table('commercial_document_items as items')
            ->join('commercial_documents as documents', 'documents.id', '=', 'items.commercial_document_id')
            ->join('products', 'products.id', '=', 'items.product_id')
            ->where('documents.shop_id', $shopId)
            ->when($godownId, fn (Builder $query) => $query->where('documents.godown_id', $godownId))
            ->where('documents.status', 'posted')
            ->whereIn('documents.type', self::SALES_TYPES)
            ->whereDate('documents.document_date', '>=', $from->toDateString())
            ->whereDate('documents.document_date', '<=', $to->toDateString())
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.purchase_price')
            ->select('products.id', 'products.name', 'products.sku')
            ->selectRaw("SUM(CASE WHEN documents.type = 'sales_return' THEN -items.quantity ELSE items.quantity END) as quantity")
            ->selectRaw("SUM(CASE WHEN documents.type = 'sales_return' THEN -(items.line_total - items.tax_amount) ELSE items.line_total - items.tax_amount END) as revenue")
            ->selectRaw("SUM(CASE WHEN documents.type = 'sales_return' THEN -(items.quantity * products.purchase_price) ELSE items.quantity * products.purchase_price END) as cost")
            ->orderByDesc('revenue')
            ->limit(8)
            ->get()
            ->map(function ($row) {
                $row->quantity = (float) $row->quantity;
                $row->revenue = (float) $row->revenue;
                $row->cost = (float) $row->cost;
                $row->profit = $row->revenue - $row->cost;
                $row->margin = abs($row->revenue) > 0.01 ? round(($row->profit / $row->revenue) * 100, 1) : 0;

                return $row;
            });
    }

    private function stockAnalytics(int $shopId, int $godownId): array
    {
        if (! $shopId) {
            return $this->emptyStockAnalytics();
        }

        $base = DB::table('inventory_balances as balances')
            ->join('products', 'products.id', '=', 'balances.product_id')
            ->where('balances.shop_id', $shopId)
            ->when($godownId, fn (Builder $query) => $query->where('balances.godown_id', $godownId));

        $summary = (clone $base)
            ->selectRaw('COALESCE(SUM(balances.quantity), 0) as quantity')
            ->selectRaw('COALESCE(SUM(balances.quantity * balances.average_cost), 0) as cost_value')
            ->selectRaw('COALESCE(SUM(balances.quantity * products.sale_price), 0) as retail_value')
            ->first();

        $productStock = (clone $base)
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.reorder_level', 'products.sale_price')
            ->select('products.id', 'products.name', 'products.sku', 'products.reorder_level', 'products.sale_price')
            ->selectRaw('SUM(balances.quantity) as quantity')
            ->selectRaw('SUM(balances.quantity * balances.average_cost) as cost_value')
            ->selectRaw('SUM(balances.quantity * products.sale_price) as retail_value');

        $lowStockQuery = (clone $productStock)
            ->havingRaw('SUM(balances.quantity) <= products.reorder_level');
        $lowStockCount = DB::query()->fromSub(clone $lowStockQuery, 'low_stock_products')->count();

        $lowStock = $lowStockQuery
            ->orderByRaw('(products.reorder_level - SUM(balances.quantity)) DESC')
            ->limit(8)
            ->get()
            ->map(function ($row) {
                $row->quantity = (float) $row->quantity;
                $row->reorder_level = (float) $row->reorder_level;
                $row->shortage = max(0, $row->reorder_level - $row->quantity);

                return $row;
            });

        $topValue = (clone $productStock)
            ->orderByDesc('cost_value')
            ->limit(8)
            ->get()
            ->map(function ($row) {
                $row->quantity = (float) $row->quantity;
                $row->cost_value = (float) $row->cost_value;
                $row->retail_value = (float) $row->retail_value;

                return $row;
            });

        $categories = (clone $base)
            ->leftJoin('reference_masters as categories', 'categories.id', '=', 'products.category_id')
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw("COALESCE(categories.name, 'Uncategorized') as name")
            ->selectRaw('SUM(balances.quantity * balances.average_cost) as value')
            ->orderByDesc('value')
            ->limit(7)
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'value' => round((float) $row->value, 2)]);

        $godowns = (clone $base)
            ->join('godowns', 'godowns.id', '=', 'balances.godown_id')
            ->groupBy('godowns.id', 'godowns.name')
            ->select('godowns.name')
            ->selectRaw('SUM(balances.quantity * balances.average_cost) as value')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'value' => round((float) $row->value, 2)]);

        $costValue = (float) ($summary->cost_value ?? 0);
        $retailValue = (float) ($summary->retail_value ?? 0);

        return [
            'summary' => [
                'quantity' => (float) ($summary->quantity ?? 0),
                'cost_value' => $costValue,
                'retail_value' => $retailValue,
                'potential_margin' => $retailValue - $costValue,
                'low_stock_count' => $lowStockCount,
            ],
            'lowStock' => $lowStock,
            'topValue' => $topValue,
            'categories' => $categories,
            'godowns' => $godowns,
        ];
    }

    private function emptyStockAnalytics(): array
    {
        return [
            'summary' => ['quantity' => 0, 'cost_value' => 0, 'retail_value' => 0, 'potential_margin' => 0, 'low_stock_count' => 0],
            'lowStock' => collect(),
            'topValue' => collect(),
            'categories' => collect(),
            'godowns' => collect(),
        ];
    }
}

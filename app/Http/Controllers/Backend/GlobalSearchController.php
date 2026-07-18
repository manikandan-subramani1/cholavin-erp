<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\CommercialDocument;
use App\Models\Party;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $query = trim($validated['q']);
        $user = $request->user();
        $groups = collect([
            $this->moduleResults($user, $query),
            $this->partyResults($user, $query, 'customers', 'customer'),
            $this->partyResults($user, $query, 'suppliers', 'supplier'),
            $this->productResults($user, $query),
            $this->documentResults($user, $query),
            $this->paymentResults($user, $query),
        ])->filter(fn (array $group) => $group['items'] !== [])->values();

        return ResponseHelper::success('Search results loaded.', [
            'query' => $query,
            'groups' => $groups,
            'total' => $groups->sum(fn (array $group) => count($group['items'])),
        ]);
    }

    private function moduleResults(User $user, string $query): array
    {
        $modules = collect([
            ['title' => 'Dashboard', 'keywords' => 'home overview', 'permission' => 'dashboard.view', 'url' => route('admin.dashboard'), 'icon' => 'ri-dashboard-line'],
            ['title' => 'Products', 'keywords' => 'items stock product master', 'permission' => 'products.view', 'url' => route('admin.products.index'), 'icon' => 'ri-shopping-bag-3-line'],
            ['title' => 'Customers', 'keywords' => 'parties receivable', 'permission' => 'customers.view', 'url' => route('admin.parties.index', ['type' => 'customers']), 'icon' => 'ri-user-3-line'],
            ['title' => 'Suppliers', 'keywords' => 'parties payable', 'permission' => 'suppliers.view', 'url' => route('admin.parties.index', ['type' => 'suppliers']), 'icon' => 'ri-truck-line'],
            ['title' => 'Current Stock', 'keywords' => 'inventory godown shop stock', 'permission' => 'stock.view', 'url' => route('admin.stock.index'), 'icon' => 'ri-stack-line'],
            ['title' => 'Payments', 'keywords' => 'payment in payment out accounts', 'permission' => 'payments.view', 'url' => route('admin.payments.index'), 'icon' => 'ri-bank-card-line'],
            ['title' => 'Company Settings', 'keywords' => 'setup configuration settings', 'permission' => 'settings.view', 'url' => route('admin.settings.index'), 'icon' => 'ri-settings-4-line'],
        ]);

        foreach (config('erp_modules.reference', []) as $key => $config) {
            $modules->push([
                'title' => $config['title'],
                'keywords' => $key.' '.$config['group'],
                'permission' => $key.'.view',
                'url' => route('admin.'.$key.'.index'),
                'icon' => 'ri-settings-3-line',
            ]);
        }

        foreach (config('erp_modules.documents', []) as $key => $config) {
            $modules->push([
                'title' => $config['title'],
                'keywords' => $key.' '.$config['group'],
                'permission' => $key.'.view',
                'url' => route('admin.documents.index', ['module' => $key]),
                'icon' => 'ri-file-list-3-line',
            ]);
        }

        $needle = str($query)->lower()->toString();
        $items = $modules
            ->filter(fn (array $module) => $user->can($module['permission']))
            ->filter(fn (array $module) => str_contains(strtolower($module['title'].' '.$module['keywords']), $needle))
            ->take(6)
            ->map(fn (array $module) => [
                'title' => $module['title'],
                'meta' => 'Open module',
                'url' => $module['url'],
                'icon' => $module['icon'],
            ])->values()->all();

        return ['key' => 'modules', 'label' => 'Modules', 'items' => $items];
    }

    private function partyResults(User $user, string $query, string $routeType, string $partyType): array
    {
        if (! $user->can($routeType.'.view')) {
            return ['key' => $routeType, 'label' => str($routeType)->title()->toString(), 'items' => []];
        }

        $items = Party::query()
            ->forActiveShop()
            ->whereIn('type', [$partyType, 'both'])
            ->where(function (Builder $builder) use ($query): void {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('mobile', 'like', "%{$query}%")
                    ->orWhere('gstin', 'like', "%{$query}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'code', 'mobile', 'is_active'])
            ->map(fn (Party $party) => [
                'title' => $party->name,
                'meta' => collect([$party->code, $party->mobile, $party->is_active ? null : 'Inactive'])->filter()->join(' · '),
                'url' => route('admin.parties.index', ['type' => $routeType]).'?'.http_build_query(['search' => $party->code]),
                'icon' => $partyType === 'customer' ? 'ri-user-3-line' : 'ri-truck-line',
            ])->all();

        return ['key' => $routeType, 'label' => str($routeType)->title()->toString(), 'items' => $items];
    }

    private function productResults(User $user, string $query): array
    {
        if (! $user->can('products.view')) {
            return ['key' => 'products', 'label' => 'Products', 'items' => []];
        }

        $items = Product::query()
            ->where(function (Builder $builder) use ($query): void {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'sku', 'barcode', 'is_active'])
            ->map(fn (Product $product) => [
                'title' => $product->name,
                'meta' => collect([$product->sku, $product->barcode, $product->is_active ? null : 'Inactive'])->filter()->join(' · '),
                'url' => $user->can('products.update')
                    ? route('admin.products.edit', $product)
                    : route('admin.products.index').'?'.http_build_query(['search' => $product->sku ?: $product->name]),
                'icon' => 'ri-shopping-bag-3-line',
            ])->all();

        return ['key' => 'products', 'label' => 'Products', 'items' => $items];
    }

    private function documentResults(User $user, string $query): array
    {
        $modules = collect(config('erp_modules.documents', []))
            ->filter(fn (array $config, string $key) => $user->can($key.'.view'));

        if ($modules->isEmpty()) {
            return ['key' => 'documents', 'label' => 'Invoices & Documents', 'items' => []];
        }

        $typeToModule = $modules->mapWithKeys(fn (array $config, string $key) => [$config['type'] => $key]);
        $items = CommercialDocument::query()
            ->with('party:id,name')
            ->accessibleBy($user)
            ->whereIn('type', $typeToModule->keys())
            ->when(session('active_financial_year_id'), fn (Builder $builder) => $builder->where('financial_year_id', session('active_financial_year_id')))
            ->where(function (Builder $builder) use ($query): void {
                $builder->where('number', 'like', "%{$query}%")
                    ->orWhere('reference_number', 'like', "%{$query}%")
                    ->orWhereHas('party', fn (Builder $party) => $party->where('name', 'like', "%{$query}%"));
            })
            ->latest('document_date')
            ->limit(6)
            ->get(['id', 'party_id', 'type', 'number', 'reference_number', 'document_date', 'total_amount'])
            ->map(function (CommercialDocument $document) use ($typeToModule, $modules): array {
                $module = $typeToModule->get($document->type);

                return [
                    'title' => $document->number,
                    'meta' => collect([$modules->get($module)['title'] ?? null, $document->party?->name, 'Rs. '.number_format((float) $document->total_amount, 2)])->filter()->join(' · '),
                    'url' => route('admin.documents.index', ['module' => $module]).'?'.http_build_query(['search' => $document->number]),
                    'icon' => 'ri-file-list-3-line',
                ];
            })->all();

        return ['key' => 'documents', 'label' => 'Invoices & Documents', 'items' => $items];
    }

    private function paymentResults(User $user, string $query): array
    {
        if (! $user->can('payments.view')) {
            return ['key' => 'payments', 'label' => 'Payments', 'items' => []];
        }

        $items = Payment::query()
            ->with('party:id,name')
            ->accessibleBy($user)
            ->when(session('active_financial_year_id'), fn (Builder $builder) => $builder->where('financial_year_id', session('active_financial_year_id')))
            ->where(function (Builder $builder) use ($query): void {
                $builder->where('number', 'like', "%{$query}%")
                    ->orWhere('reference_number', 'like', "%{$query}%")
                    ->orWhereHas('party', fn (Builder $party) => $party->where('name', 'like', "%{$query}%"));
            })
            ->latest('payment_date')
            ->limit(5)
            ->get(['id', 'party_id', 'number', 'reference_number', 'payment_date', 'amount'])
            ->map(fn (Payment $payment) => [
                'title' => $payment->number,
                'meta' => collect([$payment->party?->name, 'Rs. '.number_format((float) $payment->amount, 2)])->filter()->join(' · '),
                'url' => route('admin.payments.index').'?'.http_build_query(['search' => $payment->number]),
                'icon' => 'ri-bank-card-line',
            ])->all();

        return ['key' => 'payments', 'label' => 'Payments', 'items' => $items];
    }
}

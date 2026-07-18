<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Requests\Products\OpeningStockRequest;
use App\Http\Requests\Products\UpdateProductPriceRequest;
use App\Http\Requests\Products\UploadProductImageRequest;
use App\Models\ActivityLog;
use App\Models\CommercialDocumentItem;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ReferenceMaster;
use App\Models\Setting;
use App\Services\PdfService;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        if ($request->ajax()) {
            return DataTables::eloquent($this->filteredQuery($request))
                ->addIndexColumn()
                ->addColumn('image_url', fn (Product $product) => $product->imageUrl())
                ->addColumn('image_preview', fn (Product $product) => '<img src="'.e($product->imageUrl()).'" alt="'.e($product->name).'" class="rounded" style="width:54px;height:54px;object-fit:cover">')
                ->addColumn('category_name', fn (Product $product) => $product->category?->name ?: '-')
                ->addColumn('brand_name', fn (Product $product) => $product->brand?->name ?: '-')
                ->addColumn('variant_name', fn (Product $product) => $product->variant?->name ?: '-')
                ->addColumn('grade_name', fn (Product $product) => $product->grade?->name ?: '-')
                ->addColumn('unit_name', fn (Product $product) => $product->unitMaster?->name ?: ($product->unit ?: '-'))
                ->addColumn('current_stock', fn (Product $product) => round((float) ($product->available_stock ?? 0), 3))
                ->addColumn('godown_stock', fn (Product $product) => round((float) ($product->godown_stock ?? 0), 3))
                ->addColumn('shop_stock', fn (Product $product) => round((float) ($product->shop_stock ?? 0), 3))
                ->addColumn('stock_value', fn (Product $product) => round((float) ($product->stock_value ?? 0), 2))
                ->addColumn('stock_status', function (Product $product) {
                    $quantity = (float) ($product->available_stock ?? 0);
                    if ($quantity <= 0) return '<span class="badge bg-danger-subtle text-danger">Out of stock</span>';
                    if ($quantity <= (float) $product->reorder_level) return '<span class="badge bg-warning-subtle text-warning">Low stock</span>';
                    return '<span class="badge bg-success-subtle text-success">Good</span>';
                })
                ->editColumn('purchase_price', fn (Product $product) => '&#8377;'.number_format((float) $product->purchase_price, 2))
                ->editColumn('sale_price', fn (Product $product) => '&#8377;'.number_format((float) ($product->retail_price ?: $product->sale_price ?: $product->price), 2))
                ->editColumn('is_active', fn (Product $product) => '<span class="badge bg-'.($product->is_active ? 'success' : 'secondary').'-subtle text-'.($product->is_active ? 'success' : 'secondary').'">'.($product->is_active ? 'Active' : 'Hidden').'</span>')
                ->editColumn('show_on_homepage', fn (Product $product) => $product->show_on_homepage ? '<span class="badge bg-primary-subtle text-primary">Homepage</span>' : '<span class="badge bg-light text-muted">Products only</span>')
                ->addColumn('action', fn (Product $product) => view('backend.products.partials.actions', compact('product'))->render())
                ->rawColumns(['image_preview', 'stock_status', 'is_active', 'show_on_homepage', 'action'])
                ->toJson();
        }
        return view('backend.products.index', [
            'metrics' => $this->productMetrics(),
            'masters' => $this->masterOptions(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);
        return view('backend.products.form', ['product' => new Product] + $this->masterOptions());
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->safe()->except('image');
        $data['is_active'] = $request->boolean('is_active');
        $data['show_on_homepage'] = $request->boolean('show_on_homepage');
        return ResponseHelper::success('Product created successfully.', $this->products->create($data, $request->file('image')), 201);
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);
        return view('backend.products.form', ['product' => $product] + $this->masterOptions());
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->safe()->except(['image', 'opening_stock']);
        $data['is_active'] = $request->boolean('is_active');
        $data['show_on_homepage'] = $request->boolean('show_on_homepage');
        return ResponseHelper::success('Product updated successfully.', $this->products->update($product, $data, $request->file('image')));
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);
        $this->products->delete($product);
        return ResponseHelper::success('Product deleted successfully.');
    }

    public function pdf(Request $request, PdfService $pdf): Response
    {
        Gate::authorize('products.export');
        $rows = $this->filteredQuery($request)->orderBy('name')->get()->map(fn (Product $product) => [
            'name' => $product->name,
            'sku' => $product->sku ?: '—',
            'sale_price' => number_format((float) ($product->sale_price ?: $product->price), 2),
            'unit' => $product->unit ?: '—',
            'homepage' => $product->show_on_homepage ? 'Yes' : 'No',
            'status' => $product->is_active ? 'Active' : 'Inactive',
        ]);
        $settings = Setting::values();

        return $pdf->reportDownload('pdf.modules.reference-masters', [
            'rows' => $rows,
            'columns' => ['name' => 'Product', 'sku' => 'SKU', 'sale_price' => 'Sale Price', 'unit' => 'Unit', 'homepage' => 'Homepage', 'status' => 'Status'],
            'filters' => $request->only(['status', 'homepage', 'category_id', 'search']),
            'company' => ['name' => $settings['company_name'] ?? 'Cholavin', 'address' => $settings['company_address'] ?? ''],
            'generatedAt' => now(),
        ], 'products-'.now()->format('Ymd-His').'.pdf', 'Product Report', 'L');
    }

    public function drawer(Product $product): JsonResponse
    {
        $this->authorize('view', $product);
        $product->load(['category:id,name', 'brand:id,name', 'variant:id,name', 'grade:id,name', 'unitMaster:id,name']);
        $stock = $this->stockQuery(request())->where('product_id', $product->id)
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity, COALESCE(SUM(quantity * average_cost), 0) as value')->first();

        return ResponseHelper::success('Item details loaded.', [
            'html' => view('backend.products.partials.drawer', compact('product', 'stock'))->render(),
        ]);
    }

    public function tab(Request $request, Product $product, string $tab): JsonResponse
    {
        $this->authorize('view', $product);
        $data = ['product' => $product, 'tab' => $tab, 'records' => collect()];
        if ($tab === 'overview') $product->load(['category', 'brand', 'variant', 'grade', 'unitMaster', 'taxRate', 'hsnSac']);
        if ($tab === 'pricing') $data['records'] = $product->priceHistory()->with('creator:id,name')->limit(25)->get();
        if ($tab === 'stock') $data['records'] = $this->stockQuery($request)->where('product_id', $product->id)->with(['godown:id,name', 'shop:id,name'])->get();
        if ($tab === 'stock-history') $data['records'] = InventoryMovement::query()->accessibleBy($request->user())->where('product_id', $product->id)->latest('movement_date')->limit(50)->get();
        if (in_array($tab, ['purchase-history', 'sales-history', 'documents'], true)) $data['records'] = $this->documentItems($request, $product, $tab);
        if ($tab === 'images') $data['records'] = $product->images()->get();
        if ($tab === 'audit') $data['records'] = ActivityLog::query()->with('user:id,name')->where('auditable_type', Product::class)->where('auditable_id', $product->id)->latest('created_at')->limit(50)->get();

        return ResponseHelper::success('Item tab loaded.', ['html' => view('backend.products.partials.drawer-tab', $data)->render()]);
    }

    public function updatePrices(UpdateProductPriceRequest $request, Product $product): JsonResponse
    {
        return ResponseHelper::success('Prices updated successfully.', $this->products->updatePrices($product, $request->validated()), refresh: ['datatable' => true, 'summary' => true, 'drawer' => true]);
    }

    public function uploadImage(UploadProductImageRequest $request, Product $product): JsonResponse
    {
        return ResponseHelper::success('Product image uploaded successfully.', $this->products->uploadGalleryImage($product, $request->file('image'), $request->validated()), refresh: ['datatable' => true, 'summary' => true, 'drawer' => true]);
    }

    public function generateBarcode(Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        return ResponseHelper::success('Barcode generated successfully.', $this->products->generateBarcode($product), refresh: ['datatable' => true, 'summary' => true, 'drawer' => true]);
    }

    public function openingStock(OpeningStockRequest $request, Product $product): JsonResponse
    {
        return ResponseHelper::success('Opening stock posted to the stock ledger.', $this->products->postOpeningStock($product, $request->validated()), refresh: ['datatable' => true, 'summary' => true, 'drawer' => true]);
    }

    public function duplicate(Product $product): JsonResponse
    {
        $this->authorize('create', Product::class);
        $copy = $this->products->duplicate($product);
        return ResponseHelper::success('Item duplicated as an inactive draft.', ['id' => $copy->id, 'edit_url' => route('admin.products.edit', $copy)], 201);
    }

    public function status(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $product->update($data);
        return ResponseHelper::success($product->is_active ? 'Item activated.' : 'Item deactivated.', $product);
    }


    private function productMetrics(): array
    {
        $products = Product::query()
            ->select(['id', 'image', 'barcode', 'is_active', 'reorder_level'])
            ->selectSub($this->stockSubquery(request(), 'quantity'), 'scoped_stock');
        $summary = DB::query()->fromSub($products, 'product_scope')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->selectRaw('SUM(CASE WHEN scoped_stock > 0 AND scoped_stock <= reorder_level THEN 1 ELSE 0 END) as low_stock')
            ->selectRaw('SUM(CASE WHEN scoped_stock <= 0 THEN 1 ELSE 0 END) as out_of_stock')
            ->selectRaw("SUM(CASE WHEN image IS NULL OR image = '' THEN 1 ELSE 0 END) as without_image")
            ->selectRaw("SUM(CASE WHEN barcode IS NULL OR barcode = '' THEN 1 ELSE 0 END) as without_barcode")
            ->first();
        $stockValue = (clone $this->stockQuery(request()))->selectRaw('COALESCE(SUM(quantity * average_cost), 0) as value')->value('value');

        return [
            'total' => (int) ($summary->total ?? 0),
            'active' => (int) ($summary->active ?? 0),
            'low_stock' => (int) ($summary->low_stock ?? 0),
            'out_of_stock' => (int) ($summary->out_of_stock ?? 0),
            'stock_value' => round((float) $stockValue, 2),
            'without_image' => (int) ($summary->without_image ?? 0),
            'without_barcode' => (int) ($summary->without_barcode ?? 0),
        ];
    }

    private function stockQuery(Request $request): Builder
    {
        return InventoryBalance::query()->accessibleBy($request->user());
    }

    private function stockSubquery(Request $request, string $aggregate): Builder
    {
        $expression = $aggregate === 'value'
            ? 'COALESCE(SUM(quantity * average_cost), 0)'
            : 'COALESCE(SUM(quantity), 0)';

        return $this->stockQuery($request)
            ->selectRaw($expression)
            ->whereColumn('product_id', 'products.id');
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = Product::query()
            ->select(['id', 'category_id', 'brand_id', 'variant_id', 'grade_id', 'unit_id', 'name', 'sku', 'barcode', 'image', 'price', 'purchase_price', 'sale_price', 'wholesale_price', 'retail_price', 'unit', 'opening_stock', 'reorder_level', 'is_active', 'show_on_homepage', 'sort_order'])
            ->with(['category:id,name', 'brand:id,name', 'variant:id,name', 'grade:id,name', 'unitMaster:id,name'])
            ->selectSub($this->stockSubquery($request, 'quantity'), 'available_stock')
            ->selectSub($this->stockSubquery($request, 'quantity'), 'godown_stock')
            ->selectSub($this->stockSubquery($request, 'quantity'), 'shop_stock')
            ->selectSub($this->stockSubquery($request, 'value'), 'stock_value')
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('brand_id'), fn ($query) => $query->where('brand_id', $request->integer('brand_id')))
            ->when($request->filled('variant_id'), fn ($query) => $query->where('variant_id', $request->integer('variant_id')))
            ->when($request->filled('grade_id'), fn ($query) => $query->where('grade_id', $request->integer('grade_id')))
            ->when($request->filled('unit_id'), fn ($query) => $query->where('unit_id', $request->integer('unit_id')))
            ->when($request->filled('code'), fn ($query) => $query->where('sku', 'like', '%'.$request->string('code').'%'))
            ->when($request->filled('barcode'), fn ($query) => $query->where('barcode', 'like', '%'.$request->string('barcode').'%'))
            ->when(is_array($request->input('search')) ? data_get($request->input('search'), 'value') : $request->input('search'), function ($query, $search) {
                $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")->orWhere('barcode', 'like', "%{$search}%"));
            });

        if ($request->filled('stock_status')) {
            $stock = $this->stockSubquery($request, 'quantity');
            $sql = '('.$stock->toSql().')';
            $status = $request->string('stock_status')->toString();
            if ($status === 'out') $query->whereRaw($sql.' <= 0', $stock->getBindings());
            if ($status === 'low') $query->whereRaw($sql.' > 0 AND '.$sql.' <= products.reorder_level', array_merge($stock->getBindings(), $stock->getBindings()));
            if ($status === 'available') $query->whereRaw($sql.' > products.reorder_level', $stock->getBindings());
        }

        return $query;
    }

    private function documentItems(Request $request, Product $product, string $tab)
    {
        $types = match ($tab) {
            'purchase-history' => ['purchase_bill', 'goods_receipt', 'purchase_return'],
            'sales-history' => ['sales_invoice', 'pos_invoice', 'sales_return'],
            default => array_column(config('erp_modules.documents', []), 'type'),
        };

        return CommercialDocumentItem::query()
            ->where('product_id', $product->id)
            ->with(['document.party:id,name'])
            ->whereHas('document', fn ($documents) => $documents
                ->accessibleBy($request->user())
                ->whereIn('type', $types)
                ->when(session('active_financial_year_id'), fn ($query) => $query->where('financial_year_id', session('active_financial_year_id'))))
            ->latest('id')->limit(50)->get();
    }

    private function masterOptions(): array
    {
        $types = ['category', 'brand', 'variant', 'grade', 'unit', 'tax_rate', 'hsn_sac'];
        $masters = ReferenceMaster::query()->whereIn('type', $types)->where('is_active', true)->orderBy('name')->get()->groupBy('type');
        return [
            'categories' => $masters->get('category', collect()),
            'brands' => $masters->get('brand', collect()),
            'variants' => $masters->get('variant', collect()),
            'grades' => $masters->get('grade', collect()),
            'units' => $masters->get('unit', collect()),
            'taxRates' => $masters->get('tax_rate', collect()),
            'hsnCodes' => $masters->get('hsn_sac', collect()),
        ];
    }
}

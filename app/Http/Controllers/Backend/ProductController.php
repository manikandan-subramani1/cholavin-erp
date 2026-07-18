<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
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
                ->addColumn('image_preview', fn (Product $product) => '<img src="'.e($product->imageUrl()).'" alt="'.e($product->name).'" class="rounded" style="width:54px;height:54px;object-fit:cover">')
                ->editColumn('price', fn (Product $product) => 'Rs. '.number_format((float) ($product->sale_price ?: $product->price), 2))
                ->editColumn('is_active', fn (Product $product) => '<span class="badge bg-'.($product->is_active ? 'success' : 'secondary').'-subtle text-'.($product->is_active ? 'success' : 'secondary').'">'.($product->is_active ? 'Active' : 'Hidden').'</span>')
                ->editColumn('show_on_homepage', fn (Product $product) => $product->show_on_homepage ? '<span class="badge bg-primary-subtle text-primary">Homepage</span>' : '<span class="badge bg-light text-muted">Products only</span>')
                ->addColumn('action', fn (Product $product) => view('backend.products.partials.actions', compact('product'))->render())
                ->rawColumns(['image_preview', 'is_active', 'show_on_homepage', 'action'])
                ->toJson();
        }
        return view('backend.products.index', [
            'metrics' => [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
                'homepage' => Product::where('show_on_homepage', true)->count(),
            ],
            'categories' => ReferenceMaster::ofType('category')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
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

    private function filteredQuery(Request $request): Builder
    {
        return Product::query()
            ->select(['id', 'category_id', 'name', 'sku', 'image', 'price', 'sale_price', 'unit', 'is_active', 'show_on_homepage', 'sort_order'])
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->when($request->filled('homepage'), fn ($query) => $query->where('show_on_homepage', $request->boolean('homepage')))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')));
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

<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Models\Product;
use App\Models\ReferenceMaster;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        if ($request->ajax()) {
            $query = Product::query()
                ->select(['id', 'name', 'sku', 'image', 'price', 'sale_price', 'unit', 'is_active', 'show_on_homepage', 'sort_order'])
                ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
                ->latest('id');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('image_preview', fn (Product $product) => '<img src="'.e($product->imageUrl()).'" alt="'.e($product->name).'" class="rounded" style="width:54px;height:54px;object-fit:cover">')
                ->editColumn('price', fn (Product $product) => 'Rs. '.number_format((float) ($product->sale_price ?: $product->price), 2))
                ->editColumn('is_active', fn (Product $product) => '<span class="badge bg-'.($product->is_active ? 'success' : 'secondary').'-subtle text-'.($product->is_active ? 'success' : 'secondary').'">'.($product->is_active ? 'Active' : 'Hidden').'</span>')
                ->editColumn('show_on_homepage', fn (Product $product) => $product->show_on_homepage ? '<span class="badge bg-primary-subtle text-primary">Homepage</span>' : '<span class="badge bg-light text-muted">Products only</span>')
                ->addColumn('action', fn (Product $product) => view('backend.products.partials.actions', compact('product'))->render())
                ->rawColumns(['image_preview', 'is_active', 'show_on_homepage', 'action'])
                ->toJson();
        }
        return view('backend.products.index');
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

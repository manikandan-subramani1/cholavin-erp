<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::query()->latest('id');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('image_preview', function (Product $product) {
                    return '<img src="' . e($product->imageUrl()) . '" alt="' . e($product->name) . '" class="rounded" style="width:54px;height:54px;object-fit:cover;">';
                })
                ->editColumn('price', fn (Product $product) => $product->price ? 'Rs. ' . number_format((float) $product->price, 2) : '-')
                ->editColumn('is_active', function (Product $product) {
                    $class = $product->is_active ? 'success' : 'secondary';
                    $text = $product->is_active ? 'Active' : 'Hidden';
                    return '<span class="badge bg-' . $class . '-subtle text-' . $class . '">' . $text . '</span>';
                })
                ->editColumn('show_on_homepage', function (Product $product) {
                    return $product->show_on_homepage
                        ? '<span class="badge bg-primary-subtle text-primary">Homepage</span>'
                        : '<span class="badge bg-light text-muted">Products only</span>';
                })
                ->addColumn('action', function (Product $product) {
                    return view('backend.products.partials.actions', compact('product'))->render();
                })
                ->rawColumns(['image_preview', 'is_active', 'show_on_homepage', 'action'])
                ->toJson();
        }

        return view('backend.products.index');
    }

    public function create()
    {
        return view('backend.products.form', ['product' => new Product()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['show_on_homepage'] = $request->boolean('show_on_homepage');

        if ($request->hasFile('image')) {
            $image = Helper::uploadImage($request->file('image'), 'products');
            if ($image['status']) {
                $data['image'] = $image['name'];
            }
        }

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('backend.products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product->id);
        $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        $data['is_active'] = $request->boolean('is_active');
        $data['show_on_homepage'] = $request->boolean('show_on_homepage');

        if ($request->hasFile('image')) {
            Helper::unlinkImage($product->image);
            $image = Helper::uploadImage($request->file('image'), 'products');
            if ($image['status']) {
                $data['image'] = $image['name'];
            }
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        Helper::unlinkImage($product->image);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'sub_title' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $count = 1;

        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}

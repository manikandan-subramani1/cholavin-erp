<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function home(): View
    {
        $homepageProducts = Product::query()
            ->select([
                'id', 'name', 'slug', 'sub_title', 'description', 'image', 'price', 'sale_price', 'unit',
                'category_id', 'brand_id', 'variant_id', 'grade_id', 'unit_id', 'sort_order',
            ])
            ->with([
                'category:id,name',
                'brand:id,name',
                'variant:id,name',
                'grade:id,name',
                'unitMaster:id,name',
            ])
            ->where('is_active', true)
            ->where('show_on_homepage', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('frontend.pages.home', compact('homepageProducts'));
    }

    public function index(Request $request)
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(6);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('frontend.partials.product-cards', compact('products'))->render(),
                'next_page_url' => $products->nextPageUrl(),
            ]);
        }

        return view('frontend.pages.products', compact('products'));
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
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

    public function homeSection()
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('show_on_homepage', true)
            ->orderBy('sort_order')
            ->latest('id')
            ->take(6)
            ->get();

        return view('frontend.partials.home-products', compact('products'));
    }
}

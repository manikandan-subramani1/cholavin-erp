@if($products->isNotEmpty())
<section class="dynamic-home-products sp2">
    <div class="container">
        <div class="row align-items-end mb-4">
            <div class="col-lg-8">
                <div class="title-area1 heading2">
                    <span class="sub-title"><img src="{{ asset('frontend/assets/img/icon/left_icon_hm2_about.webp') }}" alt=""> Featured Rice <img src="{{ asset('frontend/assets/img/icon/right_icon_hm2_about.webp') }}" alt=""></span>
                    <h2 class="title text-anime-style-3">Popular Products From Our Store</h2>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0"><a href="{{ route('frontend.products') }}" class="vl-btnhm2">View All Products</a></div>
        </div>
        <div class="row g-4">
            @include('frontend.partials.product-cards', ['products' => $products])
        </div>
    </div>
</section>
@endif

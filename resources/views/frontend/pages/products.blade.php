@extends('frontend.layouts.app')

@section('title', 'Products | Cholavin')

@push('styles')
<style>
    .dynamic-products-hero { background: linear-gradient(135deg, rgba(94,0,27,.88), rgba(143,0,40,.72)), url('{{ asset('frontend/assets/img/hero/about-us-inr-herothumb.webp') }}') center/cover; padding: 150px 0 90px; color: #fff; }
    .dynamic-product-card { height: 100%; background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 40px rgba(94, 0, 27, .08); transform: translateY(20px); opacity: 0; animation: productRise .55s ease forwards; transition: transform .3s ease, box-shadow .3s ease; }
    .dynamic-product-card:hover { transform: translateY(-8px); box-shadow: 0 24px 55px rgba(94, 0, 27, .16); }
    .product-media { position: relative; aspect-ratio: 4 / 3; background: #fff8e6; overflow: hidden; }
    .product-media img { width: 100%; height: 100%; object-fit: contain; padding: 28px; transition: transform .45s ease; }
    .dynamic-product-card:hover .product-media img { transform: scale(1.06) rotate(1deg); }
    .price-chip { position: absolute; left: 18px; bottom: 18px; background: #8f0028; color: #f4c430; padding: 8px 14px; border-radius: 999px; font-size: 13px; font-weight: 800; }
    .product-info { padding: 24px; }
    .product-kicker { display: inline-block; color: #8f0028; font-weight: 800; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 8px; }
    .product-info h3 { font-size: 22px; line-height: 1.25; color: #5e001b; margin-bottom: 8px; }
    .product-info .sub-title { color: #b88719; font-weight: 700; margin-bottom: 8px; }
    .product-info p { color: #665; font-size: 14px; line-height: 1.7; }
    .product-enquiry-btn { display: inline-flex; align-items: center; gap: 8px; margin-top: 12px; color: #8f0028; font-weight: 800; }
    .product-loader { display: none; color: #8f0028; font-weight: 800; }
    @keyframes productRise { to { transform: translateY(0); opacity: 1; } }
</style>
@endpush

@section('content')
<section class="dynamic-products-hero">
    <div class="container">
        <span class="d-inline-block mb-2 fw-bold" style="color:#f4c430;">Premium Rice Collection</span>
        <h1 class="text-white mb-2">Rice Varieties We Supply</h1>
        <p class="mb-0" style="max-width:640px;color:rgba(255,255,255,.84);">Explore daily rice, biriyani rice, raw rice, and bulk supply options from Cholavin.</p>
    </div>
</section>

<section class="sp2" style="background:#fff8e6;">
    <div class="container">
        <div id="productGrid" class="row g-4">
            @include('frontend.partials.product-cards', ['products' => $products])
        </div>
        <div class="text-center mt-5">
            <div id="productLoader" class="product-loader">Loading more products...</div>
            <button id="loadMoreProducts" class="vl-btnhm2" data-next="{{ $products->nextPageUrl() }}" @if(!$products->hasMorePages()) style="display:none" @endif>Load More</button>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const button = document.getElementById('loadMoreProducts');
    const grid = document.getElementById('productGrid');
    const loader = document.getElementById('productLoader');
    let loading = false;

    function loadProducts() {
        if (!button || loading || !button.dataset.next) return;
        loading = true;
        loader.style.display = 'block';
        button.style.display = 'none';

        fetch(button.dataset.next, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(data => {
                grid.insertAdjacentHTML('beforeend', data.html);
                button.dataset.next = data.next_page_url || '';
                button.style.display = data.next_page_url ? 'inline-flex' : 'none';
            })
            .finally(() => {
                loader.style.display = 'none';
                loading = false;
            });
    }

    button?.addEventListener('click', loadProducts);
    window.addEventListener('scroll', function () {
        if (!button || !button.dataset.next) return;
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 700) {
            loadProducts();
        }
    });
})();
</script>
@endpush

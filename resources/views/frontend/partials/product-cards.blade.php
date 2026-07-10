@foreach($products as $product)
    <div class="col-xl-4 col-md-6 product-card-wrap">
        <article class="dynamic-product-card">
            <div class="product-media">
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
                @if($product->price)
                    <span class="price-chip">Rs. {{ number_format((float) $product->price, 0) }}{{ $product->unit ? ' / ' . $product->unit : '' }}</span>
                @endif
            </div>
            <div class="product-info">
                <span class="product-kicker">Cholavin Rice</span>
                <h3>{{ $product->name }}</h3>
                @if($product->sub_title)<p class="sub-title">{{ $product->sub_title }}</p>@endif
                @if($product->description)<p>{{ \Illuminate\Support\Str::limit($product->description, 115) }}</p>@endif
                <a href="{{ route('frontend.contact') }}" class="product-enquiry-btn">Enquire Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </article>
    </div>
@endforeach

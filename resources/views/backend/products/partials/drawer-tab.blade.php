@if($tab === 'overview')
<div class="product-detail-grid">
    @foreach([
        'Item code'=>$product->sku, 'Barcode'=>$product->barcode, 'Category'=>$product->category?->name,
        'Rice variety'=>$product->variant?->name, 'Grade'=>$product->grade?->name, 'Brand'=>$product->brand?->name,
        'Unit'=>$product->unitMaster?->name ?: $product->unit, 'HSN / SAC'=>$product->hsnSac?->name,
        'GST / Tax'=>$product->taxRate?->name, 'Reorder level'=>number_format((float)$product->reorder_level,3),
    ] as $label=>$value)<div><small>{{ $label }}</small><strong>{{ $value ?: '—' }}</strong></div>@endforeach
</div>
@elseif($tab === 'pricing')
<div class="product-current-prices"><span><small>Purchase</small><strong>&#8377;{{ number_format((float)$product->purchase_price,2) }}</strong></span><span><small>Selling</small><strong>&#8377;{{ number_format((float)$product->sale_price,2) }}</strong></span><span><small>Wholesale</small><strong>&#8377;{{ number_format((float)$product->wholesale_price,2) }}</strong></span><span><small>Retail</small><strong>&#8377;{{ number_format((float)$product->retail_price,2) }}</strong></span></div>
@include('backend.products.partials.record-table', ['headers'=>['Changed','Purchase','Selling','Wholesale','Retail','By'], 'rows'=>$records->map(fn($r)=>[$r->created_at?->format('d M Y H:i'),'₹'.number_format((float)$r->purchase_price,2),'₹'.number_format((float)$r->sale_price,2),'₹'.number_format((float)$r->wholesale_price,2),'₹'.number_format((float)$r->retail_price,2),$r->creator?->name ?: 'System'])])
@elseif($tab === 'stock')
@include('backend.products.partials.record-table', ['headers'=>['Godown','Shop','Batch','Quantity','Average Cost','Value'], 'rows'=>$records->map(fn($r)=>[$r->godown?->name,$r->shop?->name,$r->batch_number ?: '—',number_format((float)$r->quantity,3),'₹'.number_format((float)$r->average_cost,2),'₹'.number_format((float)$r->quantity*(float)$r->average_cost,2)])])
@elseif($tab === 'stock-history')
@include('backend.products.partials.record-table', ['headers'=>['Date','Type','Reference','Quantity','Rate','Value'], 'rows'=>$records->map(fn($r)=>[$r->movement_date?->format('d M Y'),str($r->type)->replace('_',' ')->title(),$r->reference_number ?: '—',number_format((float)$r->quantity,3),'₹'.number_format((float)$r->rate,2),'₹'.number_format((float)$r->value,2)])])
@elseif(in_array($tab, ['purchase-history','sales-history','documents']))
@include('backend.products.partials.record-table', ['headers'=>['Date','Type','Reference','Party','Quantity','Rate'], 'rows'=>$records->map(fn($r)=>[$r->document?->document_date?->format('d M Y'),str($r->document?->type)->replace('_',' ')->title(),$r->document?->number,$r->document?->party?->name ?: '—',number_format((float)$r->quantity,3),'₹'.number_format((float)$r->rate,2)])])
@elseif($tab === 'images')
<div class="product-gallery">@forelse($records as $image)<figure><img src="{{ asset($image->path) }}" alt="{{ $image->alt_text }}"><figcaption>{{ $image->is_primary ? 'Primary image' : ($image->alt_text ?: 'Product image') }}</figcaption></figure>@empty @include('backend.products.partials.empty-tab', ['message'=>'No gallery images uploaded.']) @endforelse</div>
@elseif($tab === 'barcode')
<div class="product-barcode-card"><i class="ri-barcode-line"></i><strong data-product-barcode-value>{{ $product->barcode ?: 'No barcode generated' }}</strong><small>{{ $product->name }} · {{ $product->sku ?: 'No item code' }}</small>@can('update',$product)<div><button class="btn btn-primary" type="button" data-drawer-action="barcode">{{ $product->barcode ? 'Use Existing Barcode' : 'Generate Barcode' }}</button>@if($product->barcode)<button class="btn btn-secondary" type="button" data-drawer-action="print-barcode">Print Label</button>@endif</div>@endcan</div>
@elseif($tab === 'audit')
@include('backend.products.partials.record-table', ['headers'=>['Time','User','Event','Action'], 'rows'=>$records->map(fn($r)=>[$r->created_at?->format('d M Y H:i'),$r->user?->name ?: 'System',$r->event,$r->action])])
@else
@include('backend.products.partials.empty-tab', ['message'=>'No linked documents are available in the current scope.'])
@endif

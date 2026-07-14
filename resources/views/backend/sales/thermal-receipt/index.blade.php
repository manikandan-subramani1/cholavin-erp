@extends('backend.layouts.app')

@section('title', 'Thermal Sales Receipt Sample | Cholavin ERP')

@push('styles')
<style>
    .receipt-workspace{display:grid;grid-template-columns:minmax(280px,360px) 1fr;gap:24px;align-items:start}.receipt-controls{position:sticky;top:90px}.receipt-preview{min-height:650px;padding:28px;display:flex;justify-content:center;background:#e9e3e4;border-radius:14px}.thermal-receipt{box-sizing:border-box;width:{{ $paperWidth }}mm;max-width:100%;padding:3mm;background:#fff;color:#000;font-family:"Courier New",monospace;font-size:10px;line-height:1.3;box-shadow:0 8px 30px rgba(0,0,0,.16)}.receipt-header{text-align:center}.receipt-header h1{font-size:17px;margin:0 0 2px;font-weight:800}.receipt-header h2{font-size:12px;margin:8px 0 5px;padding:4px 0;border-top:1px dashed #000;border-bottom:1px dashed #000;text-transform:uppercase}.receipt-meta{padding-bottom:5px;border-bottom:1px dashed #000}.receipt-meta>div,.receipt-totals>div{display:flex;justify-content:space-between;gap:8px}.receipt-meta strong{text-align:right}.receipt-items{width:100%;border-collapse:collapse;table-layout:fixed;margin-top:4px}.receipt-items th,.receipt-items td{padding:3px 1px;vertical-align:top}.receipt-items th{border-bottom:1px solid #000;text-align:left}.receipt-items th:first-child,.receipt-items td:first-child{width:43%;overflow-wrap:anywhere}.receipt-items .number{text-align:right;white-space:nowrap}.record-count{text-align:right;border-top:1px dashed #000;padding:4px 0}.receipt-totals{margin-left:18%;padding-top:2px}.receipt-totals .grand-total{font-size:12px;margin:3px 0;padding:4px 0;border-top:1px solid #000;border-bottom:1px double #000}.receipt-footer{text-align:center;border-top:1px dashed #000;margin-top:7px;padding-top:6px}.receipt-footer p{margin:0 0 4px}.sample-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:20px;background:#fff8e6;color:#8b6500;font-weight:700}
    @media(max-width:991px){.receipt-workspace{grid-template-columns:1fr}.receipt-controls{position:static}}
    @media print{@page{size:{{ $paperWidth }}mm auto;margin:0}body *{visibility:hidden!important}.thermal-receipt,.thermal-receipt *{visibility:visible!important}.thermal-receipt{position:absolute;left:0;top:0;width:{{ $paperWidth }}mm;max-width:none;padding:3mm;box-shadow:none}.receipt-preview{padding:0;background:#fff}}
</style>
@endpush

@section('content')
    <div class="page-title-box d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div><span class="erp-eyebrow">Sales / Print setup</span><h4>Thermal Receipt Sample</h4><p class="text-muted mb-0">Check the receipt fields and paper width before connecting it to actual sales.</p></div>
        <span class="sample-badge"><i class="ri-flask-line"></i> Sample data only</span>
    </div>

    <div class="receipt-workspace">
        <aside class="card erp-panel receipt-controls">
            <div class="card-header"><h5 class="mb-0">Printer setup</h5></div>
            <div class="card-body">
                <form method="get" action="{{ route('admin.sales.thermal-receipt.index') }}">
                    <label for="paperWidth" class="form-label">Thermal paper width</label>
                    <select id="paperWidth" name="paper_width" class="form-select mb-3" onchange="this.form.submit()">
                        <option value="80" @selected($paperWidth === 80)>80 mm (recommended)</option>
                        <option value="58" @selected($paperWidth === 58)>58 mm</option>
                    </select>
                </form>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-brand" id="printReceipt"><i class="ri-printer-line me-1"></i> Print sample</button>
                    <a class="btn btn-outline-brand" href="{{ route('admin.sales.thermal-receipt.pdf', ['paper_width' => $paperWidth]) }}"><i class="ri-file-pdf-2-line me-1"></i> Download mPDF</a>
                </div>
                <hr>
                <p class="small text-muted mb-0">In the browser print dialog select your thermal printer, use the matching paper size, set margins to None, and disable headers and footers.</p>
            </div>
        </aside>
        <div class="receipt-preview">@include('backend.sales.thermal-receipt.partials.receipt')</div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('printReceipt').addEventListener('click', function () {
        window.print();
    });
</script>
@endpush

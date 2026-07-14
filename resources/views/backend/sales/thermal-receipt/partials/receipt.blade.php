<article id="thermalReceipt" class="thermal-receipt" aria-label="Sales receipt preview">
    <header class="receipt-header">
        <h1>{{ $receipt['company']['name'] }}</h1>
        @if($receipt['company']['tagline'])<div>{{ $receipt['company']['tagline'] }}</div>@endif
        @if($receipt['company']['branch'])<strong>{{ $receipt['company']['branch'] }}</strong>@endif
        <div>{{ $receipt['company']['address'] }}</div>
        @if($receipt['company']['phone'])<div>Phone: {{ $receipt['company']['phone'] }}</div>@endif
        @if($receipt['company']['email'])<div>{{ $receipt['company']['email'] }}</div>@endif
        <h2>{{ $receipt['title'] }}</h2>
    </header>

    <section class="receipt-meta">
        <div><span>Invoice</span><strong>{{ $receipt['invoice_number'] }}</strong></div>
        <div><span>Date</span><strong>{{ $receipt['sold_at']->format('d-m-Y h:i A') }}</strong></div>
        <div><span>Cashier</span><strong>{{ $receipt['cashier'] }}</strong></div>
        <div><span>Customer</span><strong>{{ $receipt['customer']['name'] }}</strong></div>
        @if($receipt['customer']['mobile'])<div><span>Mobile</span><strong>{{ $receipt['customer']['mobile'] }}</strong></div>@endif
    </section>

    <table class="receipt-items">
        <thead><tr><th>Item</th><th class="number">Qty</th><th class="number">Rate</th><th class="number">Amount</th></tr></thead>
        <tbody>
            @foreach($receipt['items'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="number">{{ rtrim(rtrim(number_format($item['quantity'], 2), '0'), '.') }}</td>
                    <td class="number">{{ number_format($item['rate'], 2) }}</td>
                    <td class="number">{{ number_format($item['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="record-count">Total items: {{ count($receipt['items']) }}</div>
    <section class="receipt-totals">
        <div><span>Subtotal</span><strong>₹{{ number_format($receipt['totals']['subtotal'], 2) }}</strong></div>
        <div><span>Discount</span><strong>- ₹{{ number_format($receipt['totals']['discount'], 2) }}</strong></div>
        <div><span>Tax</span><strong>₹{{ number_format($receipt['totals']['tax'], 2) }}</strong></div>
        <div><span>Round off</span><strong>₹{{ number_format($receipt['totals']['round_off'], 2) }}</strong></div>
        <div class="grand-total"><span>Grand Total</span><strong>₹{{ number_format($receipt['totals']['grand_total'], 2) }}</strong></div>
        <div><span>Paid ({{ $receipt['payment_mode'] }})</span><strong>₹{{ number_format($receipt['totals']['paid'], 2) }}</strong></div>
        <div><span>Balance</span><strong>₹{{ number_format($receipt['totals']['balance'], 2) }}</strong></div>
    </section>

    <footer class="receipt-footer">
        <p>{{ $receipt['notes'] }}</p>
        <small>Generated: {{ $receipt['generated_at']->format('d-m-Y h:i A') }}</small>
    </footer>
</article>

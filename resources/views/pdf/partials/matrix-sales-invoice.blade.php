@php
    $mxCompany = \App\Support\CompanyProfile::data();
    $mxRefLabel = $invoice->salesOrder ? 'Sales Order' : 'Surat Jalan';
    $mxRefValue = $invoice->salesOrder?->number ?? $invoice->deliveryOrder?->number;
@endphp

<div class="mx-company">
    <div class="mx-brand">{{ $mxCompany['name'] }}</div>
    @if (!empty($mxCompany['address']) || !empty($mxCompany['city']))
        <div>{{ trim(($mxCompany['address'] ?? '') . ($mxCompany['address'] && $mxCompany['city'] ? ', ' : '') . ($mxCompany['city'] ?? '')) }}</div>
    @endif
    @if (!empty($mxCompany['email']) || !empty($mxCompany['phone']))
        <div>{{ trim(implode(' • ', array_filter([$mxCompany['email'] ?? '', $mxCompany['phone'] ?? '']))) }}</div>
    @endif
</div>

<div class="mx-row hd-row">
    <div>
        <div class="mx-title">Sales Invoice</div>
        <div style="font-weight:700;">{{ $invoice->number }}</div>
    </div>
    <div class="mx-meta">
        <div><span class="k">Invoice Date:</span> {{ $invoice->invoice_date?->format('d M Y') }}</div>
        <div><span class="k">Due Date:</span> {{ $invoice->due_date?->format('d M Y') }}</div>
        @if ($mxRefValue)
            <div><span class="k">{{ $mxRefLabel }}:</span> {{ $mxRefValue }}</div>
        @endif
        <div><span class="k">Status:</span> {{ ucfirst($invoice->status) }}</div>
    </div>
</div>

<div class="mx-row" style="margin-top:6px;"><span class="k">Bill To</span><span>{{ $invoice->customer?->name }}</span></div>
@if ($invoice->customer?->address)
    <div class="mx-row"><span class="k"></span><span>{{ $invoice->customer->address }}</span></div>
@endif

<div class="mx-items">
    <div class="mx-row hd">
        <span class="num">#</span>
        <span class="desc">Item</span>
        <span class="rt">Qty</span>
        <span class="rt">Price</span>
        <span class="rt">Disc</span>
        <span class="rt">Tax</span>
        <span class="rt">Total</span>
    </div>
    @forelse ($invoice->items as $index => $item)
        <div class="mx-row">
            <span class="num">{{ $index + 1 }}</span>
            <span class="desc">{{ $item->product?->name ?: $item->description }}</span>
            <span class="rt">{{ number_format((float) $item->qty, 2) }}</span>
            <span class="rt">{{ number_format((float) $item->unit_price, 2) }}</span>
            <span class="rt">{{ number_format((float) $item->discount, 0) }}%</span>
            <span class="rt">{{ $item->tax ? $item->tax->rate.'%' : '—' }}</span>
            <span class="rt" style="font-weight:700;">{{ number_format((float) $item->line_total, 2) }}</span>
        </div>
    @empty
        <div class="mx-row"><span>No items.</span></div>
    @endforelse
</div>

<div class="mx-sum">
    <div class="mx-row"><span>Subtotal</span><span>{{ number_format((float) $invoice->subtotal, 2) }}</span></div>
    <div class="mx-row"><span>Discount</span><span>-{{ number_format((float) $invoice->discount_amount, 2) }}</span></div>
    <div class="mx-row"><span>Tax</span><span>{{ number_format((float) $invoice->tax_amount, 2) }}</span></div>
    <div class="mx-row" style="font-weight:700;"><span>Total</span><span>{{ number_format((float) $invoice->total, 2) }}</span></div>
    <div class="mx-row"><span>Paid</span><span>-{{ number_format((float) $invoice->paid_amount, 2) }}</span></div>
    <div class="mx-row" style="font-weight:700;"><span>Balance</span><span>{{ number_format($invoice->balance(), 2) }}</span></div>
</div>

@if ($invoice->notes)
    <div class="mx-row" style="margin-top:2px;"><span class="k">Notes</span></div>
    <div>{{ $invoice->notes }}</div>
@endif

<div class="mx-sign">
    <div class="col">
        <div class="lbl">Prepared By</div>
        <div class="line"></div>
        <div class="mx-center">{{ $invoice->creator?->name }}</div>
    </div>
    <div class="col">
        <div class="lbl">Approved By</div>
        <div class="line"></div>
        <div class="mx-center">Management</div>
    </div>
</div>

<div class="mx-center mx-footer">{{ now()->format('d M Y H:i') }}</div>
@php
    $c = $company ?? \App\Support\CompanyProfile::data();
    $logo = $logo ?? \App\Support\CompanyProfile::logoUrl();
@endphp
<div class="pl-company" style="{{ !empty($logo) ? 'display:flex;align-items:center;gap:12px;' : '' }}">
    @if (!empty($logo))
        <img src="{{ $logo }}" alt="logo" style="max-height:48px;max-width:160px;object-fit:contain;">
    @endif
    <div>
        <div class="pl-company-name">{{ $c['name'] }}</div>
        @if (!empty($c['address']) || !empty($c['city']))
            <div class="pl-company-addr">{{ trim(($c['address'] ?? '') . ($c['address'] && $c['city'] ? ', ' : '') . ($c['city'] ?? '')) }}</div>
        @endif
        @if (!empty($c['email']) || !empty($c['phone']))
            <div class="pl-company-contact">{{ trim(implode(' • ', array_filter([$c['email'] ?? '', $c['phone'] ?? '']))) }}</div>
        @endif
    </div>
</div>

<div class="pl-header">
    <div>
        <div class="pl-title">Purchase Order</div>
        <div style="font-size:13px;font-weight:700;margin-top:4px;">{{ $purchaseOrder->number }}</div>
    </div>
    <div class="pl-meta">
        <div><span>Order Date:</span> {{ $purchaseOrder->order_date?->format('d M Y') }}</div>
        <div><span>Expected:</span> {{ $purchaseOrder->expected_date?->format('d M Y') ?: '—' }}</div>
        <div><span>Status:</span> {{ ucfirst($purchaseOrder->status) }}</div>
    </div>
</div>

<div style="font-size:11px;margin-bottom:12px;">
    <div><b>Supplier:</b> {{ $purchaseOrder->supplier?->name }}</div>
    @if ($purchaseOrder->supplier?->address)
        <div style="margin-top:2px;">{{ $purchaseOrder->supplier->address }}</div>
    @endif
</div>

<table>
    <thead>
        <tr>
            <th style="width:5%;">#</th>
            <th style="width:46%;">Item</th>
            <th class="r" style="width:9%;">Qty</th>
            <th class="r" style="width:12%;">Price</th>
            <th class="r" style="width:8%;">Disc</th>
            <th class="r" style="width:8%;">Tax</th>
            <th class="r" style="width:12%;">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($purchaseOrder->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product?->name ?: $item->description }}</td>
                <td class="r">{{ number_format((float) $item->qty, 2) }}</td>
                <td class="r">{{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="r">{{ number_format((float) $item->discount, 0) }}%</td>
                <td class="r">{{ $item->tax ? $item->tax->rate.'%' : '—' }}</td>
                <td class="r" style="font-weight:700;">{{ number_format((float) $item->line_total, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center;">No items.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="pl-summary">
    <div class="row">
        <span>Subtotal</span>
        <span>{{ number_format((float) $purchaseOrder->subtotal, 2) }}</span>
    </div>
    <div class="row">
        <span>Discount</span>
        <span>-{{ number_format((float) $purchaseOrder->discount_amount, 2) }}</span>
    </div>
    <div class="row">
        <span>Tax</span>
        <span>{{ number_format((float) $purchaseOrder->tax_amount, 2) }}</span>
    </div>
    <div class="row total">
        <span>Total</span>
        <span>{{ number_format((float) $purchaseOrder->total, 2) }}</span>
    </div>
</div>

@if ($purchaseOrder->notes)
    <div class="pl-notes"><b>Notes:</b> {{ $purchaseOrder->notes }}</div>
@endif

<div class="pl-sign">
    <div class="col">
        <div class="lbl">Prepared By</div>
        <div class="line"></div>
        <div class="sub">{{ $purchaseOrder->creator?->name }}</div>
    </div>
    <div class="col">
        <div class="lbl">Approved By</div>
        <div class="line"></div>
        <div class="sub">Management</div>
    </div>
</div>

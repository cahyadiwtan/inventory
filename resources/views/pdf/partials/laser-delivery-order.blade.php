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
        <div class="pl-title">Delivery Order</div>
        <div style="font-size:13px;font-weight:700;margin-top:4px;">{{ $deliveryOrder->number }}</div>
    </div>
    <div class="pl-meta">
        <div><span>Date:</span> {{ $deliveryOrder->delivery_date?->format('d M Y') }}</div>
        <div><span>Sales Order:</span> {{ $deliveryOrder->salesOrder?->number }}</div>
        <div><span>Warehouse:</span> {{ $deliveryOrder->warehouse?->name }}</div>
        <div><span>Status:</span> {{ ucfirst($deliveryOrder->status) }}</div>
    </div>
</div>

<div style="font-size:11px;margin-bottom:12px;">
    <div><b>Ship To:</b> {{ $deliveryOrder->salesOrder?->customer?->name }}</div>
    @if ($deliveryOrder->salesOrder?->customer?->address)
        <div style="margin-top:2px;">{{ $deliveryOrder->salesOrder->customer->address }}</div>
    @endif
</div>

<table>
    <thead>
        <tr>
            <th style="width:5%;">#</th>
            <th style="width:55%;">Item</th>
            <th class="r" style="width:14%;">Order</th>
            <th class="r" style="width:14%;">Delivered</th>
            <th class="r" style="width:12%;">Qty</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($deliveryOrder->items as $index => $item)
            @php
                $soItem = $item->salesOrderItem;
                $deliveredBefore = $soItem ? round($salesService->deliveredQty($soItem) - (float) $item->qty, 2) : 0;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product?->name ?: $soItem?->description }}</td>
                <td class="r">{{ number_format((float) ($soItem?->qty ?? 0), 2) }}</td>
                <td class="r">{{ number_format($deliveredBefore, 2) }}</td>
                <td class="r" style="font-weight:700;">{{ number_format((float) $item->qty, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center;">No items.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="pl-summary">
    <div class="row total">
        <span>Total Qty</span>
        <span>{{ number_format((float) $deliveryOrder->items->sum('qty'), 2) }}</span>
    </div>
</div>

@if ($deliveryOrder->notes)
    <div class="pl-notes"><b>Notes:</b> {{ $deliveryOrder->notes }}</div>
@endif

<div class="pl-sign">
    <div class="col">
        <div class="lbl">Prepared By</div>
        <div class="line"></div>
        <div class="sub">{{ $deliveryOrder->creator?->name }}</div>
    </div>
    <div class="col">
        <div class="lbl">Received By</div>
        <div class="line"></div>
        <div class="sub">{{ $deliveryOrder->salesOrder?->customer?->name }}</div>
    </div>
</div>

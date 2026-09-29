@php
    $mxCompany = \App\Support\CompanyProfile::data();
    $mxCustomer = $deliveryOrder->salesOrder?->customer ?? $deliveryOrder->salesInvoice?->customer;
    $mxSoNumber = $deliveryOrder->salesOrder?->number ?? $deliveryOrder->salesInvoice?->number;
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
        <div class="mx-title">Delivery Order</div>
        <div style="font-weight:700;">{{ $deliveryOrder->number }}</div>
    </div>
    <div class="mx-meta">
        <div><span class="k">Date:</span> {{ $deliveryOrder->delivery_date?->format('d M Y') }}</div>
        @if ($mxSoNumber)
            <div><span class="k">Ref:</span> {{ $mxSoNumber }}</div>
        @endif
    </div>
</div>

<div class="mx-row" style="margin-top:6px;"><span class="k">Ship To</span><span>{{ $mxCustomer?->name }}</span></div>
@if ($mxCustomer?->address)
    <div class="mx-row"><span class="k"></span><span>{{ $mxCustomer->address }}</span></div>
@endif

<div class="mx-items">
    <div class="mx-row hd">
        <span class="num">#</span>
        <span class="desc">Item</span>
        <span class="rt">Qty</span>
    </div>
    @forelse ($deliveryOrder->items as $index => $item)
        @php
            $soItem = $item->salesOrderItem;
            $deliveredBefore = $soItem ? round($salesService->deliveredQty($soItem) - (float) $item->qty, 2) : 0;
        @endphp
        <div class="mx-row">
            <span class="num">{{ $index + 1 }}</span>
            <span class="desc">{{ $item->product?->name ?: $soItem?->description }}</span>
            <span class="rt" style="font-weight:700;">{{ number_format((float) $item->qty, 2) }}</span>
        </div>
    @empty
        <div class="mx-row"><span>No items.</span></div>
    @endforelse
</div>

<div class="mx-sum mx-row">
    <span class="k">Total Qty</span>
    <span>{{ number_format((float) $deliveryOrder->items->sum('qty'), 2) }}</span>
</div>

@if ($deliveryOrder->notes)
    <div class="mx-row" style="margin-top:2px;"><span class="k">Notes</span></div>
    <div>{{ $deliveryOrder->notes }}</div>
@endif

<div class="mx-sign">
    <div class="col">
        <div class="lbl">Prepared By</div>
        <div class="line"></div>
        <div class="mx-center">{{ $deliveryOrder->creator?->name }}</div>
    </div>
    <div class="col">
        <div class="lbl">Received By</div>
        <div class="line"></div>
        <div class="mx-center">{{ $mxCustomer?->name }}</div>
    </div>
</div>

<div class="mx-center mx-footer">{{ now()->format('d M Y H:i') }}</div>
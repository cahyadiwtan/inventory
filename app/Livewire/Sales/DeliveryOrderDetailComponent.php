<?php

namespace App\Livewire\Sales;

use App\Models\DeliveryOrder;
use App\Services\SalesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class DeliveryOrderDetailComponent extends Component
{
    public DeliveryOrder $deliveryOrder;

    public string $printMode = 'laser';

    public function mount(DeliveryOrder $deliveryOrder): void
    {
        $this->deliveryOrder = $deliveryOrder->loadMissing([
            'salesOrder.customer',
            'warehouse',
            'items.product',
            'items.salesOrderItem',
            'creator',
        ]);
    }

    public function print(): void
    {
        $this->dispatch('print');
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $data = [
            'deliveryOrder' => $this->deliveryOrder->load([
                'salesOrder.customer',
                'warehouse',
                'items.product',
                'items.salesOrderItem',
                'creator',
            ]),
            'salesService' => app(SalesService::class),
            'company' => \App\Support\CompanyProfile::data(),
            'logo' => \App\Support\CompanyProfile::logoDataUri(),
        ];

        $pdf = Pdf::loadView('pdf.delivery-order', $data)->setPaper('a4');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'delivery-order-'.$this->deliveryOrder->number.'-'.now()->format('Ymd-His').'.pdf'
        );
    }

    public function render()
    {
        $deliveryOrder = $this->deliveryOrder->load([
            'salesOrder.customer',
            'warehouse',
            'items.product',
            'items.salesOrderItem',
            'creator',
        ]);

        return view('livewire.sales.delivery-order-detail', [
            'deliveryOrder' => $deliveryOrder,
            'salesService' => app(SalesService::class),
        ])->title("{$deliveryOrder->number} | Inventory System");
    }
}

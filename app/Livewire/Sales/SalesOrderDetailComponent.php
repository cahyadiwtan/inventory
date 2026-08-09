<?php

namespace App\Livewire\Sales;

use App\Models\SalesOrder;
use App\Services\SalesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class SalesOrderDetailComponent extends Component
{
    public SalesOrder $salesOrder;

    public function mount(SalesOrder $salesOrder): void
    {
        $this->salesOrder = $salesOrder->loadMissing([
            'customer',
            'quotation',
            'items.product',
            'items.tax',
            'creator',
            'deliveryOrders',
            'invoice',
        ]);
    }

    public function print(): void
    {
        $this->dispatch('print');
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $data = [
            'salesOrder' => $this->salesOrder->load([
                'customer',
                'quotation',
                'items.product',
                'items.tax',
                'creator',
            ]),
            'salesService' => app(SalesService::class),
            'company' => \App\Support\CompanyProfile::data(),
            'logo' => \App\Support\CompanyProfile::logoDataUri(),
        ];

        $pdf = Pdf::loadView('pdf.sales-order', $data)->setPaper('a4');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'sales-order-'.$this->salesOrder->number.'-'.now()->format('Ymd-His').'.pdf'
        );
    }

    public function render()
    {
        $salesOrder = $this->salesOrder->load([
            'customer',
            'quotation',
            'items.product',
            'items.tax',
            'creator',
            'deliveryOrders',
            'invoice',
        ]);

        return view('livewire.sales.sales-order-detail', [
            'salesOrder' => $salesOrder,
            'salesService' => app(SalesService::class),
        ])->title("{$salesOrder->number} | Inventory System");
    }
}

<?php

namespace App\Livewire\Sales;

use App\Models\DeliveryOrder;
use App\Models\SalesInvoice;
use App\Services\SalesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class DirectInvoiceDetailComponent extends Component
{
    public SalesInvoice $invoice;

    public string $printMode = 'laser';

    public function mount(SalesInvoice $invoice): void
    {
        $this->invoice = $invoice->loadMissing([
            'customer',
            'deliveryOrder.warehouse',
            'items.product',
            'items.tax',
            'creator',
        ]);
    }

    public function print(): void
    {
        $this->dispatch('print');
    }

    public function exportPdfInvoice(): \Symfony\Component\HttpFoundation\Response
    {
        $data = [
            'invoice' => $this->invoice->load([
                'customer',
                'items.product',
                'items.tax',
                'creator',
            ]),
            'company' => \App\Support\CompanyProfile::data(),
            'logo' => \App\Support\CompanyProfile::logoDataUri(),
        ];

        $pdf = Pdf::loadView('pdf.sales-invoice', $data)->setPaper('a4');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'invoice-'.$this->invoice->number.'-'.now()->format('Ymd-His').'.pdf'
        );
    }

    public function exportPdfSuratJalan(): \Symfony\Component\HttpFoundation\Response
    {
        $delivery = $this->invoice->deliveryOrder()->with([
            'warehouse',
            'items.product',
            'creator',
        ])->firstOrFail();

        $data = [
            'deliveryOrder' => $delivery,
            'salesService' => app(SalesService::class),
            'company' => \App\Support\CompanyProfile::data(),
            'logo' => \App\Support\CompanyProfile::logoDataUri(),
        ];

        $pdf = Pdf::loadView('pdf.delivery-order', $data)->setPaper('a4');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'surat-jalan-'.$delivery->number.'-'.now()->format('Ymd-His').'.pdf'
        );
    }

    public function render()
    {
        $invoice = $this->invoice->load([
            'customer',
            'deliveryOrder.warehouse',
            'items.product',
            'items.tax',
            'creator',
        ]);

        return view('livewire.sales.direct-invoice-detail', [
            'invoice' => $invoice,
            'deliveryOrder' => $invoice->deliveryOrder,
            'salesService' => app(SalesService::class),
        ])->title("{$invoice->number} | Inventory System");
    }
}
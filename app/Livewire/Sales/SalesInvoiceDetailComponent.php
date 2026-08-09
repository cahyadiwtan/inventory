<?php

namespace App\Livewire\Sales;

use App\Models\SalesInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class SalesInvoiceDetailComponent extends Component
{
    public SalesInvoice $invoice;

    public string $printMode = 'laser';

    public function mount(SalesInvoice $invoice): void
    {
        $this->invoice = $invoice->loadMissing([
            'customer',
            'salesOrder',
            'items.product',
            'items.tax',
            'creator',
            'payments.creator',
        ]);
    }

    public function print(): void
    {
        $this->dispatch('print');
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $data = [
            'invoice' => $this->invoice->load([
                'customer',
                'salesOrder',
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
            'sales-invoice-'.$this->invoice->number.'-'.now()->format('Ymd-His').'.pdf'
        );
    }

    public function render()
    {
        $invoice = $this->invoice->load([
            'customer',
            'salesOrder',
            'items.product',
            'items.tax',
            'creator',
            'payments.creator',
        ]);

        return view('livewire.sales.sales-invoice-detail', [
            'invoice' => $invoice,
        ])->title("{$invoice->number} | Inventory System");
    }
}

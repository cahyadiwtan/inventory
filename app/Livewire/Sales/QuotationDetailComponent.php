<?php

namespace App\Livewire\Sales;

use App\Models\Quotation;
use App\Services\SalesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class QuotationDetailComponent extends Component
{
    public Quotation $quotation;

    public function mount(Quotation $quotation): void
    {
        $this->quotation = $quotation->loadMissing([
            'customer',
            'items.product',
            'items.tax',
            'creator',
            'salesOrder',
        ]);
    }

    public function print(): void
    {
        $this->dispatch('print');
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $data = [
            'quotation' => $this->quotation->load('customer', 'items.product', 'items.tax', 'creator'),
            'company' => \App\Support\CompanyProfile::data(),
            'logo' => \App\Support\CompanyProfile::logoDataUri(),
        ];

        $pdf = Pdf::loadView('pdf.quotation', $data)->setPaper('a4');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'quotation-'.$this->quotation->number.'-'.now()->format('Ymd-His').'.pdf'
        );
    }

    public function render()
    {
        $quotation = $this->quotation->load('customer', 'items.product', 'items.tax', 'creator', 'salesOrder');

        return view('livewire.sales.quotation-detail', [
            'quotation' => $quotation,
            'salesService' => app(SalesService::class),
        ])->title("{$quotation->number} | Inventory System");
    }
}

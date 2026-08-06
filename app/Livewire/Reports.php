<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Reports extends Component
{
    public string $report = 'stock_on_hand';
    public string $group = 'Stok';
    public ?string $from = null;
    public ?string $to = null;
    public ?string $warehouse = null;
    public ?string $customer = null;
    public ?string $supplier = null;

    public function selectReport(string $key, string $group): void
    {
        $this->report = $key;
        $this->group = $group;
        $this->reset(['from', 'to', 'warehouse', 'customer', 'supplier']);
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $payload = $this->result();
        $filename = 'laporan-'.str_replace('_', '-', $this->report).'-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new \App\Exports\ReportExport($payload['title'], $payload['headings'], $payload['rows']), $filename);
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $payload = $this->result();
        $pdf = Pdf::loadView('pdf.report', $payload)->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print($pdf->output()), 'laporan-'.str_replace('_', '-', $this->report).'-'.now()->format('Ymd-His').'.pdf');
    }

    public function result(): array
    {
        return app(ReportService::class)->run($this->report, [
            'from' => $this->from,
            'to' => $this->to,
            'warehouse' => $this->warehouse,
            'customer' => $this->customer,
            'supplier' => $this->supplier,
        ]);
    }

    public function render()
    {
        return view('livewire.reports', [
            'reportService' => app(ReportService::class),
            'groups' => app(ReportService::class)->groups(),
            'result' => $this->result(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ])->title('Laporan | Inventory System');
    }
}

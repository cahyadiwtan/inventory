<?php

namespace App\Livewire;

use App\Exports\MasterDataExport;
use App\Imports\RawArrayImport;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\ActivityLogService;
use App\Support\DataTransferConfig;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class DataTransfer extends Component
{
    use WithFileUploads;

    public string $entity = 'customers';

    public $file;

    public bool $importing = false;

    public array $summary = [];

    /** @var array<int, string> */
    public array $importErrors = [];

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $config = DataTransferConfig::config($this->entity);

        $rows = ($config['model'])::query()
            ->orderBy('code')
            ->get()
            ->map(fn ($record) => $this->recordToRow($record, $config))
            ->values()
            ->all();

        $filename = str_replace('_', '-', $this->entity).'-'.now()->format('Ymd-His').'.xlsx';

        app(ActivityLogService::class)->log('export', null, "exported {$this->entity} data");

        return Excel::download(
            new MasterDataExport($config['title'], DataTransferConfig::headings($this->entity), $rows),
            $filename,
        );
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $config = DataTransferConfig::config($this->entity);
        $example = [];

        foreach ($config['fields'] as $field => $meta) {
            $example[$field] = match ($meta['type']) {
                'boolean' => 1,
                'number' => $field === 'rate' ? 11 : 0,
                default => $field,
            };
        }

        $filename = 'template-'.str_replace('_', '-', $this->entity).'.xlsx';

        return Excel::download(
            new MasterDataExport($config['title'], DataTransferConfig::headings($this->entity), [$example]),
            $filename,
        );
    }

    public function import(): void
    {
        $this->validate(['entity' => ['required', 'string'], 'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120']]);

        $this->importing = true;
        $this->importErrors = [];

        try {
            $config = DataTransferConfig::config($this->entity);

            $rows = Excel::toArray(new RawArrayImport(), $this->file->getRealPath());
            $sheet = $rows[0] ?? [];
            $heading = array_shift($sheet);

            if (empty($heading)) {
                $this->importErrors[] = 'File kosong atau tidak memiliki baris header.';

                return;
            }

            $headings = DataTransferConfig::headings($this->entity);
            $columnIndex = $this->mapHeadings($heading, $headings, $config);

            if ($columnIndex === null) {
                $this->importErrors[] = 'Kolom header tidak cocok. Silakan unduh template terbaru.';

                return;
            }

            $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0];
            $rowErrors = [];

            foreach ($sheet as $line => $row) {
                [$data, $rowError] = $this->mapRow($row, $columnIndex, $config);

                if ($rowError) {
                    $summary['skipped']++;
                    $rowErrors[] = "Baris ".($line + 2).": ".$rowError;

                    continue;
                }

                $result = $this->persist($data, $config);

                if ($result === 'created') {
                    $summary['created']++;
                } elseif ($result === 'updated') {
                    $summary['updated']++;
                } else {
                    $summary['skipped']++;
                    $rowErrors[] = "Baris ".($line + 2).": ".$result;
                }
            }

            if ($summary['created'] + $summary['updated'] > 0) {
                app(ActivityLogService::class)->log('import', null, "imported {$this->entity} data");
            }

            $this->summary = $summary;
            $this->importErrors = $rowErrors;
        } finally {
            $this->importing = false;
        }
    }

    protected function mapHeadings(array $fileHeading, array $expected, array $config): ?array
    {
        $map = [];

        foreach ($fileHeading as $i => $label) {
            $label = trim((string) $label);
            foreach ($config['fields'] as $field => $meta) {
                if ($meta['label'] === $label) {
                    $map[$field] = $i;
                }
            }
        }

        if (count($map) === 0) {
            return null;
        }

        return $map;
    }

    protected function mapRow(array $row, array $columnIndex, array $config): array
    {
        $data = [];
        $error = null;

        foreach ($config['fields'] as $field => $meta) {
            $index = $columnIndex[$field] ?? null;
            $value = $index !== null ? ($row[$index] ?? null) : null;

            if ($value === null || $value === '') {
                if (($meta['required'] ?? false) && $field !== 'code') {
                    $error = "kolom '{$meta['label']}' wajib diisi.";
                }

                continue;
            }

            if ($meta['type'] === 'number') {
                $value = (float) str_replace(',', '', (string) $value);
            } elseif ($meta['type'] === 'boolean') {
                $value = in_array(strtolower((string) $value), ['1', 'true', 'yes', 'y'], true);
            } else {
                $value = (string) $value;
            }

            if (isset($meta['lookup'])) {
                [$model, $codeField] = $meta['lookup'];
                $lookup = $model::query()->where($codeField, $value)->value('id');
                if (! $lookup) {
                    return [null, "kode '{$value}' untuk {$meta['label']} tidak ditemukan."];
                }

                $data[str_replace('_code', '_id', $field)] = $lookup;

                continue;
            }

            $data[$field] = $value;
        }

        return [$data, $error];
    }

    protected function persist(array $data, array $config): string
    {
        $model = $config['model'];
        $code = $data['code'] ?? null;

        if (! $code) {
            return 'kode tidak ditemukan.';
        }

        $record = $model::query()->where('code', $code)->first();

        if ($record) {
            $record->update($data);

            return 'updated';
        }

        $record = $model::create($data);

        return 'created';
    }

    protected function recordToRow($record, array $config): array
    {
        $row = [];

        foreach ($config['fields'] as $field => $meta) {
            if (isset($meta['lookup'])) {
                [$lookupModel, $codeField] = $meta['lookup'];
                $relationField = $this->relationKeyFor($lookupModel);
                $row[$field] = $record->{$relationField}?->{$codeField} ?? '';
            } else {
                $value = $record->{$field};

                if ($meta['type'] === 'boolean') {
                    $row[$field] = $value ? 1 : 0;
                } elseif (is_bool($value)) {
                    $row[$field] = $value ? 1 : 0;
                } else {
                    $row[$field] = $value;
                }
            }
        }

        return $row;
    }

    protected function relationKeyFor(string $model): string
    {
        return match ($model) {
            ProductCategory::class => 'category',
            ProductBrand::class => 'brand',
            Unit::class => 'unit',
            default => 'category',
        };
    }

    public function render()
    {
        $configs = [];

        foreach (DataTransferConfig::ENTITIES as $key => $config) {
            $configs[$key] = ['title' => $config['title'], 'count' => ($config['model'])::count()];
        }

        $config = DataTransferConfig::config($this->entity);

        return view('livewire.data-transfer', [
            'configs' => $configs,
            'config' => $config,
            'headings' => DataTransferConfig::headings($this->entity),
        ])->title('Import / Export Data | Inventory System');
    }
}
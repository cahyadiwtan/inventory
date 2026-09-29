<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductPrice;
use App\Models\ProductWarehouse;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCrud extends Component
{
    use WithPagination;

    public array $form = [
        'code' => '',
        'barcode' => '',
        'name' => '',
        'category_id' => '',
        'brand_id' => '',
        'unit_id' => '',
        'selling_price' => 0,
        'purchase_price' => 0,
        'min_stock' => 0,
        'max_stock' => 0,
        'reorder_point' => 0,
        'weight' => null,
    ];

    public array $barcodes = [];

    public array $prices = [];

    public array $stocks = [];

    public ?string $editingId = null;

    public bool $showModal = false;

    public string $search = '';

    public function render()
    {
        $query = Product::with(['category', 'brand', 'unit', 'warehouses']);

        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%");
            });
        }

        $items = $query->orderByDesc('created_at')->paginate(10);

        return view('livewire.product.product-crud', [
            'items' => $items,
            'categories' => ProductCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'brands' => ProductBrand::query()->where('is_active', true)->orderBy('name')->get(),
            'units' => Unit::query()->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
        ])->title('Product | Inventory System');
    }

    public function rules(): array
    {
        return [
            'form.code' => ['required', 'string', 'max:50', "unique:products,code,{$this->editingId},id"],
            'form.name' => ['required', 'string', 'max:150'],
            'form.category_id' => ['required', 'exists:product_categories,id'],
            'form.brand_id' => ['nullable', 'exists:product_brands,id'],
            'form.unit_id' => ['required', 'exists:units,id'],
            'form.selling_price' => ['required', 'numeric', 'min:0'],
            'form.purchase_price' => ['required', 'numeric', 'min:0'],
            'form.min_stock' => ['required', 'numeric', 'min:0'],
            'form.max_stock' => ['required', 'numeric', 'min:0'],
            'form.reorder_point' => ['required', 'numeric', 'min:0'],
            'form.weight' => ['nullable', 'numeric', 'min:0'],
            'barcodes.*' => ['nullable', 'string', 'max:100'],
            'prices.*.price' => ['nullable', 'numeric', 'min:0'],
            'prices.*.valid_from' => ['nullable', 'date'],
            'prices.*.valid_to' => ['nullable', 'date'],
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $product = Product::with(['barcodes', 'prices', 'warehouses'])->findOrFail($id);

        $this->form = $product->only([
            'code',
            'barcode',
            'name',
            'category_id',
            'brand_id',
            'unit_id',
            'selling_price',
            'purchase_price',
            'min_stock',
            'max_stock',
            'reorder_point',
            'weight',
        ]);

        $this->barcodes = $product->barcodes->pluck('barcode')->values()->all();
        $this->prices = $product->prices->map(fn ($p) => [
            'price_type' => $p->price_type,
            'price' => $p->price,
            'valid_from' => $p->valid_from?->format('Y-m-d'),
            'valid_to' => $p->valid_to?->format('Y-m-d'),
        ])->all();
        $this->stocks = $product->warehouses->mapWithKeys(fn ($w) => [
            $w->warehouse_id => (string) $w->qty_on_hand,
        ])->all();

        $this->editingId = $id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = $this->form;
        $data['selling_price'] = (float) $data['selling_price'];
        $data['purchase_price'] = (float) $data['purchase_price'];
        $data['min_stock'] = (float) $data['min_stock'];
        $data['max_stock'] = (float) $data['max_stock'];
        $data['reorder_point'] = (float) $data['reorder_point'];
        $data['weight'] = $data['weight'] !== '' && $data['weight'] !== null ? (float) $data['weight'] : null;
        $data['brand_id'] = $data['brand_id'] !== '' && $data['brand_id'] !== null ? $data['brand_id'] : null;
        $data['category_id'] = $data['category_id'] !== '' && $data['category_id'] !== null ? $data['category_id'] : null;
        $data['unit_id'] = $data['unit_id'] !== '' && $data['unit_id'] !== null ? $data['unit_id'] : null;

        if ($this->editingId) {
            $product = Product::findOrFail($this->editingId);
            $product->update($data);
            $message = 'Product updated.';
            $event = 'update';
        } else {
            $product = Product::create($data);
            $message = 'Product created.';
            $event = 'create';
        }

        $this->syncBarcodes($product);
        $this->syncPrices($product);
        $this->syncStocks($product);

        app(ActivityLogService::class)->log($event, $product, "{$event}d product: {$product->name}");

        session()->flash('status', $message);
        $this->resetForm();
    }

    public function delete(string $id): void
    {
        $product = Product::findOrFail($id);

        app(ActivityLogService::class)->log('delete', $product, "deleted product: {$product->name}");
        $product->delete();

        session()->flash('status', 'Product deleted.');
    }

    public function toggleActive(string $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => ! $product->is_active]);
    }

    public function addBarcode(): void
    {
        $this->barcodes[] = '';
    }

    public function removeBarcode(int $index): void
    {
        unset($this->barcodes[$index]);
        $this->barcodes = array_values($this->barcodes);
    }

    public function addPrice(): void
    {
        $this->prices[] = ['price_type' => 'selling', 'price' => 0, 'valid_from' => null, 'valid_to' => null];
    }

    public function removePrice(int $index): void
    {
        unset($this->prices[$index]);
        $this->prices = array_values($this->prices);
    }

    public function resetForm(): void
    {
        $this->form = [
            'code' => '',
            'barcode' => '',
            'name' => '',
            'category_id' => '',
            'brand_id' => '',
            'unit_id' => '',
            'selling_price' => 0,
            'purchase_price' => 0,
            'min_stock' => 0,
            'max_stock' => 0,
            'reorder_point' => 0,
            'weight' => null,
        ];
        $this->barcodes = [];
        $this->prices = [];
        $this->stocks = [];
        $this->editingId = null;
        $this->showModal = false;
    }

    protected function syncBarcodes(Product $product): void
    {
        $barcodes = array_values(array_filter($this->barcodes, fn ($b) => $b !== '' && $b !== null));

        ProductBarcode::where('product_id', $product->id)
            ->whereNotIn('barcode', $barcodes)
            ->delete();

        foreach ($barcodes as $barcode) {
            ProductBarcode::firstOrCreate(
                ['product_id' => $product->id, 'barcode' => $barcode],
            );
        }
    }

    protected function syncPrices(Product $product): void
    {
        $prices = array_values(array_filter($this->prices, fn ($p) => ($p['price'] ?? null) !== '' && ($p['price'] ?? null) !== null));

        ProductPrice::where('product_id', $product->id)->delete();

        foreach ($prices as $price) {
            ProductPrice::create([
                'product_id' => $product->id,
                'price_type' => $price['price_type'] ?? 'selling',
                'price' => (float) $price['price'],
                'valid_from' => $price['valid_from'] ?: null,
                'valid_to' => $price['valid_to'] ?: null,
            ]);
        }
    }

    protected function syncStocks(Product $product): void
    {
        foreach ($this->stocks as $warehouseId => $qty) {
            if (! $warehouseId) {
                continue;
            }

            ProductWarehouse::updateOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouseId],
                ['qty_on_hand' => (float) $qty],
            );
        }
    }
}

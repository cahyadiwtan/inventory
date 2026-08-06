<?php

namespace Tests\Feature\Product;

use App\Livewire\Product\ProductCrud;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\ProductWarehouse;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    private function makeReferences(): array
    {
        return [
            'category' => ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel']),
            'brand' => ProductBrand::create(['code' => 'BRD-01', 'name' => 'Nike']),
            'unit' => Unit::create(['code' => 'PCS', 'name' => 'Piece', 'symbol' => 'pcs']),
            'warehouse' => Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang Pusat', 'address' => 'Jl. A']),
        ];
    }

    public function test_super_admin_can_view_product_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Product');
    }

    public function test_product_can_be_created(): void
    {
        $this->actingAsAdmin();
        $refs = $this->makeReferences();

        Livewire::test(ProductCrud::class)
            ->set('form.code', 'PRD-01')
            ->set('form.name', 'Kaos Polos')
            ->set('form.category_id', $refs['category']->id)
            ->set('form.brand_id', $refs['brand']->id)
            ->set('form.unit_id', $refs['unit']->id)
            ->set('form.selling_price', 50000)
            ->set('form.purchase_price', 30000)
            ->set('form.min_stock', 5)
            ->set('form.max_stock', 200)
            ->set('form.reorder_point', 10)
            ->set('barcodes', ['89999'])
            ->set('prices', [['price_type' => 'selling', 'price' => 55000, 'valid_from' => null, 'valid_to' => null]])
            ->set('stocks', [$refs['warehouse']->id => 120])
            ->call('save');

        $this->assertDatabaseHas('products', [
            'code' => 'PRD-01',
            'name' => 'Kaos Polos',
            'selling_price' => 50000,
        ]);

        $product = Product::where('code', 'PRD-01')->first();

        $this->assertDatabaseHas('product_barcodes', [
            'product_id' => $product->id,
            'barcode' => '89999',
        ]);

        $this->assertDatabaseHas('product_prices', [
            'product_id' => $product->id,
            'price' => 55000,
        ]);

        $this->assertDatabaseHas('product_warehouses', [
            'product_id' => $product->id,
            'warehouse_id' => $refs['warehouse']->id,
            'qty_on_hand' => 120,
        ]);
    }

    public function test_product_code_is_required(): void
    {
        $this->actingAsAdmin();
        $refs = $this->makeReferences();

        Livewire::test(ProductCrud::class)
            ->set('form.code', '')
            ->set('form.name', 'Kaos')
            ->set('form.category_id', $refs['category']->id)
            ->set('form.unit_id', $refs['unit']->id)
            ->call('save')
            ->assertHasErrors(['form.code' => 'required']);
    }

    public function test_product_category_is_required(): void
    {
        $this->actingAsAdmin();
        $refs = $this->makeReferences();

        Livewire::test(ProductCrud::class)
            ->set('form.code', 'PRD-01')
            ->set('form.name', 'Kaos')
            ->set('form.category_id', '')
            ->set('form.unit_id', $refs['unit']->id)
            ->call('save')
            ->assertHasErrors(['form.category_id' => 'required']);
    }

    public function test_product_code_must_be_unique(): void
    {
        $this->actingAsAdmin();
        $refs = $this->makeReferences();
        Product::create([
            'code' => 'PRD-01',
            'name' => 'Kaos',
            'category_id' => $refs['category']->id,
            'unit_id' => $refs['unit']->id,
            'selling_price' => 0,
            'purchase_price' => 0,
            'min_stock' => 0,
            'max_stock' => 0,
            'reorder_point' => 0,
        ]);

        Livewire::test(ProductCrud::class)
            ->set('form.code', 'PRD-01')
            ->set('form.name', 'Lain')
            ->set('form.category_id', $refs['category']->id)
            ->set('form.unit_id', $refs['unit']->id)
            ->call('save')
            ->assertHasErrors(['form.code' => 'unique']);
    }

    public function test_product_can_be_updated(): void
    {
        $this->actingAsAdmin();
        $refs = $this->makeReferences();
        $product = Product::create([
            'code' => 'PRD-01',
            'name' => 'Kaos',
            'category_id' => $refs['category']->id,
            'unit_id' => $refs['unit']->id,
            'selling_price' => 50000,
            'purchase_price' => 30000,
            'min_stock' => 0,
            'max_stock' => 0,
            'reorder_point' => 0,
        ]);

        Livewire::test(ProductCrud::class)
            ->call('openEdit', $product->id)
            ->set('form.name', 'Kaos Premium')
            ->set('form.selling_price', 75000)
            ->call('save');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Kaos Premium',
            'selling_price' => 75000,
        ]);
    }

    public function test_product_can_be_deleted(): void
    {
        $this->actingAsAdmin();
        $refs = $this->makeReferences();
        $product = Product::create([
            'code' => 'PRD-01',
            'name' => 'Kaos',
            'category_id' => $refs['category']->id,
            'unit_id' => $refs['unit']->id,
            'selling_price' => 0,
            'purchase_price' => 0,
            'min_stock' => 0,
            'max_stock' => 0,
            'reorder_point' => 0,
        ]);

        Livewire::test(ProductCrud::class)
            ->call('delete', $product->id);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}

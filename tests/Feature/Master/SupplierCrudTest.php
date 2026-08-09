<?php

namespace Tests\Feature\Master;

use App\Livewire\Master\MasterCrud;
use App\Livewire\Master\SupplierFormComponent;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    public function test_super_admin_can_view_supplier_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('master.index', ['entity' => 'suppliers']))
            ->assertOk()
            ->assertSee('Supplier');
    }

    public function test_super_admin_can_view_supplier_create_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('master.suppliers.create'))
            ->assertOk()
            ->assertSee('Tambah Supplier');
    }

    public function test_super_admin_can_view_supplier_edit_page(): void
    {
        $this->actingAsAdmin();
        $supplier = Supplier::create(['code' => 'SUP-01', 'name' => 'PT Sumber Rejeki', 'address' => 'Jl. A']);

        $this->get(route('master.suppliers.edit', $supplier))
            ->assertOk()
            ->assertSee('Edit Supplier')
            ->assertSee('PT Sumber Rejeki');
    }

    public function test_supplier_can_be_created(): void
    {
        $this->actingAsAdmin();

        Livewire::test(SupplierFormComponent::class)
            ->set('form.code', 'SUP-01')
            ->set('form.name', 'PT Sumber Rejeki')
            ->set('form.address', 'Jl. Industri 2')
            ->set('form.payment_term_days', 45)
            ->set('form.credit_limit', 50000000)
            ->call('save');

        $this->assertDatabaseHas('suppliers', [
            'code' => 'SUP-01',
            'name' => 'PT Sumber Rejeki',
            'payment_term_days' => 45,
            'credit_limit' => 50000000,
        ]);
    }

    public function test_supplier_address_is_required(): void
    {
        $this->actingAsAdmin();

        Livewire::test(SupplierFormComponent::class)
            ->set('form.code', 'SUP-01')
            ->set('form.name', 'PT Sumber Rejeki')
            ->set('form.address', '')
            ->call('save')
            ->assertHasErrors(['form.address' => 'required']);
    }

    public function test_supplier_code_must_be_unique(): void
    {
        $this->actingAsAdmin();
        Supplier::create(['code' => 'SUP-01', 'name' => 'PT Sumber Rejeki', 'address' => 'Jl. A']);

        Livewire::test(SupplierFormComponent::class)
            ->set('form.code', 'SUP-01')
            ->set('form.name', 'Lain')
            ->set('form.address', 'Jl. B')
            ->call('save')
            ->assertHasErrors(['form.code' => 'unique']);
    }

    public function test_supplier_can_be_updated(): void
    {
        $this->actingAsAdmin();
        $supplier = Supplier::create(['code' => 'SUP-01', 'name' => 'PT Sumber Rejeki', 'address' => 'Jl. A']);

        Livewire::test(SupplierFormComponent::class, ['supplier' => $supplier])
            ->set('form.name', 'PT Sumber Rejeki Baru')
            ->call('save');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'PT Sumber Rejeki Baru',
        ]);
    }

    public function test_supplier_can_be_deleted(): void
    {
        $this->actingAsAdmin();
        $supplier = Supplier::create(['code' => 'SUP-01', 'name' => 'PT Sumber Rejeki', 'address' => 'Jl. A']);

        Livewire::test(MasterCrud::class, ['entity' => 'suppliers'])
            ->call('delete', $supplier->id);

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }
}

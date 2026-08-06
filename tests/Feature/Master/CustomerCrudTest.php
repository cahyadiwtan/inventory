<?php

namespace Tests\Feature\Master;

use App\Livewire\Master\MasterCrud;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    public function test_super_admin_can_view_customer_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('master.index', ['entity' => 'customers']))
            ->assertOk()
            ->assertSee('Customer');
    }

    public function test_customer_can_be_created(): void
    {
        $this->actingAsAdmin();

        Livewire::test(MasterCrud::class, ['entity' => 'customers'])
            ->set('form.code', 'CST-01')
            ->set('form.name', 'PT Maju Jaya')
            ->set('form.address', 'Jl. Merdeka 1')
            ->set('form.payment_term_days', 30)
            ->set('form.credit_limit', 100000000)
            ->call('save');

        $this->assertDatabaseHas('customers', [
            'code' => 'CST-01',
            'name' => 'PT Maju Jaya',
            'payment_term_days' => 30,
            'credit_limit' => 100000000,
        ]);
    }

    public function test_customer_address_is_required(): void
    {
        $this->actingAsAdmin();

        Livewire::test(MasterCrud::class, ['entity' => 'customers'])
            ->set('form.code', 'CST-01')
            ->set('form.name', 'PT Maju Jaya')
            ->set('form.address', '')
            ->call('save')
            ->assertHasErrors(['form.address' => 'required']);
    }

    public function test_customer_code_must_be_unique(): void
    {
        $this->actingAsAdmin();
        Customer::create(['code' => 'CST-01', 'name' => 'PT Maju Jaya', 'address' => 'Jl. A']);

        Livewire::test(MasterCrud::class, ['entity' => 'customers'])
            ->set('form.code', 'CST-01')
            ->set('form.name', 'Lain')
            ->set('form.address', 'Jl. B')
            ->call('save')
            ->assertHasErrors(['form.code' => 'unique']);
    }

    public function test_customer_can_be_updated(): void
    {
        $this->actingAsAdmin();
        $customer = Customer::create(['code' => 'CST-01', 'name' => 'PT Maju Jaya', 'address' => 'Jl. A']);

        Livewire::test(MasterCrud::class, ['entity' => 'customers'])
            ->call('openEdit', $customer->id)
            ->set('form.name', 'PT Maju Jaya Baru')
            ->call('save');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'PT Maju Jaya Baru',
        ]);
    }

    public function test_customer_can_be_deleted(): void
    {
        $this->actingAsAdmin();
        $customer = Customer::create(['code' => 'CST-01', 'name' => 'PT Maju Jaya', 'address' => 'Jl. A']);

        Livewire::test(MasterCrud::class, ['entity' => 'customers'])
            ->call('delete', $customer->id);

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }
}

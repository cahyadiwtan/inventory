<?php

namespace Tests\Feature\Master;

use App\Livewire\Master\MasterCrud;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    public function test_super_admin_can_view_category_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('master.index', ['entity' => 'categories']))
            ->assertOk()
            ->assertSee('Product Category');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('master.index', ['entity' => 'categories']))
            ->assertRedirect('/login');
    }

    public function test_category_can_be_created(): void
    {
        $this->actingAsAdmin();

        Livewire::test(MasterCrud::class, ['entity' => 'categories'])
            ->set('form.code', 'CAT-01')
            ->set('form.name', 'Apparel')
            ->call('save');

        $this->assertDatabaseHas('product_categories', [
            'code' => 'CAT-01',
            'name' => 'Apparel',
        ]);
    }

    public function test_category_code_is_required(): void
    {
        $this->actingAsAdmin();

        Livewire::test(MasterCrud::class, ['entity' => 'categories'])
            ->set('form.code', '')
            ->set('form.name', 'Apparel')
            ->call('save')
            ->assertHasErrors(['form.code' => 'required']);
    }

    public function test_category_can_be_updated(): void
    {
        $this->actingAsAdmin();
        $category = ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel']);

        Livewire::test(MasterCrud::class, ['entity' => 'categories'])
            ->call('openEdit', $category->id)
            ->set('form.name', 'Fashion')
            ->call('save');

        $this->assertDatabaseHas('product_categories', [
            'id' => $category->id,
            'name' => 'Fashion',
        ]);
    }

    public function test_category_can_be_deleted(): void
    {
        $this->actingAsAdmin();
        $category = ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel']);

        Livewire::test(MasterCrud::class, ['entity' => 'categories'])
            ->call('delete', $category->id);

        $this->assertSoftDeleted('product_categories', ['id' => $category->id]);
    }

    public function test_category_code_must_be_unique(): void
    {
        $this->actingAsAdmin();
        ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel']);

        Livewire::test(MasterCrud::class, ['entity' => 'categories'])
            ->set('form.code', 'CAT-01')
            ->set('form.name', 'Lain')
            ->call('save')
            ->assertHasErrors(['form.code' => 'unique']);
    }
}

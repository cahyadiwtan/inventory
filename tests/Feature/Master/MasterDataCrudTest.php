<?php

namespace Tests\Feature\Master;

use App\Livewire\Master\MasterCrud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MasterDataCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Entity slug -> model class + a valid form payload.
     */
    public static function entityProvider(): array
    {
        return [
            'brands' => [
                'brands',
                \App\Models\ProductBrand::class,
                ['code' => 'BRD-01', 'name' => 'Nike'],
            ],
            'units' => [
                'units',
                \App\Models\Unit::class,
                ['code' => 'PCS', 'name' => 'Piece', 'symbol' => 'pcs'],
            ],
            'warehouses' => [
                'warehouses',
                \App\Models\Warehouse::class,
                ['code' => 'WH-01', 'name' => 'Gudang Pusat', 'address' => 'Jl. A'],
            ],
            'taxes' => [
                'taxes',
                \App\Models\Tax::class,
                ['code' => 'PPN', 'name' => 'Pajak Pertambahan Nilai', 'rate' => 11],
            ],
        ];
    }

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();
        $this->actingAs(User::factory()->create()->assignRole('super admin'));
    }

    #[DataProvider('entityProvider')]
    public function test_entity_page_is_accessible(string $entity): void
    {
        $this->actingAsAdmin();

        $this->get(route('master.index', ['entity' => $entity]))->assertOk();
    }

    #[DataProvider('entityProvider')]
    public function test_entity_can_be_created(string $entity, string $model, array $form): void
    {
        $this->actingAsAdmin();

        $component = Livewire::test(MasterCrud::class, ['entity' => $entity]);

        foreach ($form as $field => $value) {
            $component->set("form.{$field}", $value);
        }

        $component->call('save');

        $this->assertDatabaseHas((new $model())->getTable(), $form);
    }

    #[DataProvider('entityProvider')]
    public function test_entity_can_be_deleted(string $entity, string $model, array $form): void
    {
        $this->actingAsAdmin();
        $record = $model::factory()->create();

        Livewire::test(MasterCrud::class, ['entity' => $entity])
            ->call('delete', $record->id);

        $this->assertSoftDeleted($record->getTable(), ['id' => $record->id]);
    }

    #[DataProvider('entityProvider')]
    public function test_entity_code_must_be_unique(string $entity, string $model, array $form): void
    {
        $this->actingAsAdmin();
        $model::factory()->create(['code' => $form['code']]);

        $component = Livewire::test(MasterCrud::class, ['entity' => $entity]);

        foreach ($form as $field => $value) {
            $component->set("form.{$field}", $value);
        }

        $component->call('save')->assertHasErrors(['form.code' => 'unique']);
    }
}

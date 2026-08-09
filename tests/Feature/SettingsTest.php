<?php

namespace Tests\Feature;

use App\Livewire\Settings\SettingsComponent;
use App\Models\Setting;
use App\Models\User;
use App\Support\CompanyProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    public function test_super_admin_can_view_settings_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Pengaturan Sistem');
    }

    public function test_settings_can_be_updated(): void
    {
        $this->actingAsAdmin();
        Setting::instance();

        Livewire::test(SettingsComponent::class)
            ->set('form.company_name', 'PT Contoh Sejahtera')
            ->set('form.address', 'Jl. Merdeka No. 1')
            ->set('form.city', 'Bandung')
            ->set('form.phone', '0812-3456')
            ->set('form.email', 'kontak@contoh.co.id')
            ->call('save');

        $this->assertDatabaseHas('settings', [
            'company_name' => 'PT Contoh Sejahtera',
            'address' => 'Jl. Merdeka No. 1',
            'city' => 'Bandung',
            'phone' => '0812-3456',
            'email' => 'kontak@contoh.co.id',
        ]);

        $this->assertSame('PT Contoh Sejahtera', CompanyProfile::name());
    }

    public function test_empty_fields_are_stored_as_null(): void
    {
        $this->actingAsAdmin();
        Setting::instance();

        Livewire::test(SettingsComponent::class)
            ->set('form.company_name', 'PT Contoh')
            ->set('form.website', '')
            ->set('form.tagline', '')
            ->call('save');

        $this->assertDatabaseHas('settings', [
            'company_name' => 'PT Contoh',
            'website' => null,
            'tagline' => null,
        ]);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->actingAsAdmin();

        Livewire::test(SettingsComponent::class)
            ->set('form.company_name', 'PT Contoh')
            ->set('form.email', 'bukan-email')
            ->call('save')
            ->assertHasErrors(['form.email' => 'email']);
    }

    public function test_logo_can_be_uploaded(): void
    {
        $this->actingAsAdmin();
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png', 100, 100);

        Livewire::test(SettingsComponent::class)
            ->set('form.company_name', 'PT Contoh')
            ->set('logo', $file)
            ->call('save');

        $setting = Setting::instance();

        $this->assertNotNull($setting->logo_path);
        Storage::disk('public')->assertExists($setting->logo_path);
        $this->assertNotNull(CompanyProfile::logoUrl());
    }

    public function test_logo_can_be_removed(): void
    {
        $this->actingAsAdmin();
        Storage::fake('public');

        $path = 'logos/logo-test.png';
        Storage::disk('public')->put($path, 'fake-image');

        Setting::instance()->update(['logo_path' => $path]);

        Livewire::test(SettingsComponent::class)
            ->call('removeLogo');

        $this->assertNull(Setting::instance()->logo_path);
        Storage::disk('public')->assertMissing($path);
    }
}

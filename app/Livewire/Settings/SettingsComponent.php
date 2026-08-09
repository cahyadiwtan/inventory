<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingsComponent extends Component
{
    use WithFileUploads;

    public array $form = [];

    public $logo;

    public function mount(): void
    {
        $this->form = Setting::instance()->only([
            'company_name',
            'tagline',
            'address',
            'city',
            'postal_code',
            'phone',
            'email',
            'website',
            'npwp',
        ]);
    }

    public function rules(): array
    {
        return [
            'form.company_name' => ['nullable', 'string', 'max:150'],
            'form.tagline' => ['nullable', 'string', 'max:255'],
            'form.address' => ['nullable', 'string', 'max:1000'],
            'form.city' => ['nullable', 'string', 'max:150'],
            'form.postal_code' => ['nullable', 'string', 'max:20'],
            'form.phone' => ['nullable', 'string', 'max:50'],
            'form.email' => ['nullable', 'email', 'max:100'],
            'form.website' => ['nullable', 'string', 'max:150'],
            'form.npwp' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $setting = Setting::instance();

        $data = $this->form;
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        if ($this->logo) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $extension = $this->logo->getClientOriginalExtension();
            $path = $this->logo->storeAs('logos', 'logo-'.now()->format('YmdHis').'.'.$extension, 'public');
            $data['logo_path'] = $path;
        }

        $setting->update($data);

        app(ActivityLogService::class)->log('update', $setting, "updated system settings: {$setting->company_name}");

        session()->flash('status', 'Pengaturan berhasil disimpan.');
    }

    public function removeLogo(): void
    {
        $setting = Setting::instance();

        if ($setting->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);
            $setting->update(['logo_path' => null]);
        }

        $this->logo = null;
    }

    public function render()
    {
        return view('livewire.settings.settings', [
            'logoUrl' => \App\Support\CompanyProfile::logoUrl(),
        ])->title('Pengaturan | Inventory System');
    }
}

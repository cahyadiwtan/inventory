<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::instance()->update([
            'company_name' => config('app.name'),
            'tagline' => 'Enterprise System',
            'address' => 'Jl. Industri Raya No. 88',
            'city' => 'Jakarta, Indonesia',
            'postal_code' => '12950',
            'phone' => '+62 21 555-0198',
            'email' => 'hello@example.com',
            'website' => 'https://example.com',
            'npwp' => '00.000.000.0-000.000',
        ]);
    }
}

<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class CompanyProfile
{
    public static function data(): array
    {
        $setting = Setting::instance();

        return [
            'name' => $setting->company_name ?: config('app.name'),
            'tagline' => $setting->tagline,
            'address' => $setting->address,
            'city' => $setting->city,
            'postal_code' => $setting->postal_code,
            'phone' => $setting->phone,
            'email' => $setting->email,
            'website' => $setting->website,
            'npwp' => $setting->npwp,
        ];
    }

    public static function name(): string
    {
        return static::data()['name'];
    }

    public static function logoUrl(): ?string
    {
        $setting = Setting::instance();

        return $setting->logo_path ? Storage::disk('public')->url($setting->logo_path) : null;
    }

    /**
     * Absolute local path used by DomPDF (remote URLs are disabled).
     */
    public static function logoPath(): ?string
    {
        $setting = Setting::instance();

        return $setting->logo_path ? Storage::disk('public')->path($setting->logo_path) : null;
    }

    /**
     * Base64 data URI — renders reliably in both browser print and DomPDF.
     */
    public static function logoDataUri(): ?string
    {
        $setting = Setting::instance();

        if (! $setting->logo_path) {
            return null;
        }

        $path = Storage::disk('public')->path($setting->logo_path);

        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $data = base64_encode(file_get_contents($path));

        return "data:{$mime};base64,{$data}";
    }
}

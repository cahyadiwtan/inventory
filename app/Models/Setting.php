<?php

namespace App\Models;

class Setting extends BaseModel
{
    protected $fillable = [
        'company_name',
        'tagline',
        'address',
        'city',
        'postal_code',
        'phone',
        'email',
        'website',
        'npwp',
        'logo_path',
    ];

    public static function instance(): self
    {
        return static::query()->firstOrCreate([], ['company_name' => config('app.name')]);
    }
}

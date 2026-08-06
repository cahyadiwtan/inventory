<?php

namespace App\Models;

class Tax extends BaseModel
{
    protected $fillable = ['code', 'name', 'rate', 'is_active'];

    protected function casts(): array
    {
        return [
            'rate' => 'float',
            'is_active' => 'boolean',
        ];
    }
}

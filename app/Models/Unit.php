<?php

namespace App\Models;

class Unit extends BaseModel
{
    protected $fillable = ['code', 'name', 'symbol', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

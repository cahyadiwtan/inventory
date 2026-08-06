<?php

namespace App\Models;

class Warehouse extends BaseModel
{
    protected $fillable = ['code', 'name', 'address', 'phone', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function productWarehouses()
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    public function stockRows()
    {
        return $this->hasMany(ProductWarehouse::class);
    }
}

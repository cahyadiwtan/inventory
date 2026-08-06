<?php

namespace Database\Factories;

use App\Models\ProductBrand;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductBrandFactory extends Factory
{
    protected $model = ProductBrand::class;

    public function definition(): array
    {
        return [
            'code' => 'BRD-' . strtoupper(fake()->unique()->bothify('####')),
            'name' => fake()->unique()->company(),
            'is_active' => true,
        ];
    }
}

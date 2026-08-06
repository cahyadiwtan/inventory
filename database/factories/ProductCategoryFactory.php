<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        return [
            'code' => 'CAT-' . strtoupper(fake()->unique()->bothify('####')),
            'name' => fake()->unique()->words(2, true),
            'is_active' => true,
        ];
    }
}

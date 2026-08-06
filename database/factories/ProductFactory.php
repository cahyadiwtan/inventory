<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductBrand;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'code' => 'PRD-' . strtoupper(fake()->unique()->bothify('####')),
            'barcode' => fake()->unique()->numerify('##########'),
            'name' => fake()->unique()->words(3, true),
            'category_id' => ProductCategory::factory(),
            'brand_id' => ProductBrand::factory(),
            'unit_id' => Unit::factory(),
            'selling_price' => fake()->randomFloat(2, 1000, 1000000),
            'purchase_price' => fake()->randomFloat(2, 500, 800000),
            'min_stock' => fake()->randomFloat(2, 0, 10),
            'max_stock' => fake()->randomFloat(2, 100, 500),
            'reorder_point' => fake()->randomFloat(2, 5, 50),
            'weight' => fake()->randomFloat(3, 0.1, 50),
            'is_active' => true,
        ];
    }
}

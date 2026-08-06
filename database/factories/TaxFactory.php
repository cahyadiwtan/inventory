<?php

namespace Database\Factories;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition(): array
    {
        return [
            'code' => 'TAX-' . fake()->unique()->bothify('###'),
            'name' => fake()->randomElement(['PPN', 'PPh 23', 'Non-Tax']),
            'rate' => fake()->randomElement([0, 11, 2]),
            'is_active' => true,
        ];
    }
}

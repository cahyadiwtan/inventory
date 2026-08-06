<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'code' => 'CST-' . strtoupper(fake()->unique()->bothify('####')),
            'name' => fake()->unique()->company(),
            'npwp' => fake()->numerify('##.###.###.#-###.###'),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'pic_name' => fake()->name(),
            'payment_term_days' => 30,
            'credit_limit' => fake()->randomElement([0, 100000000, 250000000]),
            'is_active' => true,
        ];
    }
}

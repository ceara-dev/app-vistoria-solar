<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'price_cents' => fake()->randomElement([0, 9900, 19900, 39900]),
            'limits' => [
                'users' => fake()->randomElement([null, 5, 20]),
                'trips_per_month' => fake()->randomElement([null, 50, 500]),
            ],
            'is_active' => true,
        ];
    }

    /**
     * Plano desativado para novas empresas.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}

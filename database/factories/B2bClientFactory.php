<?php

namespace Database\Factories;

use App\Models\B2bClient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<B2bClient>
 */
class B2bClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'legal_name' => fake()->company().' LTDA',
            'trade_name' => fake()->company(),
            'document' => fake()->unique()->numerify('##############'),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->unique()->safeEmail(),
            'contact_phone' => fake()->numerify('119########'),
        ];
    }
}

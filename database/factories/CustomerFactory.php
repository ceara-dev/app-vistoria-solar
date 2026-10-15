<?php

namespace Database\Factories;

use App\Enums\CustomerType;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'type' => CustomerType::Individual,
            'name' => fake()->name(),
            'document' => fake()->unique()->cpf(false),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '119'.fake()->numerify('########'),
        ];
    }

    /**
     * Pessoa jurídica, com CNPJ e nome fantasia.
     */
    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CustomerType::Company,
            'name' => fake()->company(),
            'trade_name' => fake()->company(),
            'document' => fake()->unique()->cnpj(false),
        ]);
    }
}

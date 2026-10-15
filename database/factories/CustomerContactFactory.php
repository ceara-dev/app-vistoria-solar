<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerContact>
 */
class CustomerContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * A empresa sai do cliente: `customer_id` vem antes de `tenant_id` para a
     * closure já receber o id criado.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'tenant_id' => fn (array $attributes) => Customer::withoutGlobalScope(TenantScope::class)
                ->whereKey($attributes['customer_id'])
                ->value('tenant_id'),
            'name' => fake()->name(),
            'position' => fake()->jobTitle(),
            'email' => fake()->safeEmail(),
            'phone' => '119'.fake()->numerify('########'),
        ];
    }
}

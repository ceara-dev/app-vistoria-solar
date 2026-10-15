<?php

namespace Database\Factories;

use App\Enums\BrazilianState;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerAddress>
 */
class CustomerAddressFactory extends Factory
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
            'label' => 'Residência',
            'zip_code' => fake()->numerify('########'),
            'street' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'district' => fake()->citySuffix(),
            'city' => fake()->city(),
            'state' => fake()->randomElement(BrazilianState::cases()),
        ];
    }
}

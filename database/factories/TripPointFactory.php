<?php

namespace Database\Factories;

use App\Enums\TripPointStatus;
use App\Models\Trip;
use App\Models\TripPoint;
use App\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TripPoint>
 */
class TripPointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * A empresa sai da rota: `trip_id` vem antes de `tenant_id`.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'tenant_id' => fn (array $attributes) => Trip::withoutGlobalScope(TenantScope::class)
                ->whereKey($attributes['trip_id'])
                ->value('tenant_id'),
            'latitude' => fake()->latitude(-24, -23),
            'longitude' => fake()->longitude(-47, -46),
            'recorded_at' => now(),
            'status' => TripPointStatus::Moving,
            'speed_ms' => 10,
        ];
    }
}

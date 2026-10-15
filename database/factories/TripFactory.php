<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
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
            'user_id' => User::factory(),
            'started_at' => now()->subHour(),
            'finished_at' => now(),
            'distance_m' => fake()->randomFloat(1, 500, 50000),
            'moving_seconds' => 3000,
            'paused_seconds' => 600,
            'max_speed_ms' => 16.5,
        ];
    }
}

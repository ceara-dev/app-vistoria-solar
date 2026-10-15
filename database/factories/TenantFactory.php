<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Tenant;
use App\Tenancy\TenantStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'legal_name' => fake()->company(),
            'document' => fake()->unique()->numerify('##############'),
            'status' => TenantStatus::Active,
        ];
    }

    /**
     * Empresa em período de teste ainda válido.
     */
    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TenantStatus::Trial,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    /**
     * Empresa com o período de teste vencido.
     */
    public function expiredTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TenantStatus::Trial,
            'trial_ends_at' => now()->subDay(),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TenantStatus::Suspended,
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TenantStatus::Canceled,
        ]);
    }

    /**
     * Empresa no plano informado.
     */
    public function onPlan(Plan $plan): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_id' => $plan->id,
        ]);
    }
}

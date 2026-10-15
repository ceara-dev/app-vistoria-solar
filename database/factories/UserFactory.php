<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Membro ativo da empresa, com o papel informado e ela como empresa atual.
     */
    public function forTenant(Tenant $tenant, TenantRole $role = TenantRole::Manager): static
    {
        return $this
            ->state(fn (array $attributes) => ['current_tenant_id' => $tenant->id])
            ->hasAttached($tenant, ['is_active' => true], 'tenants')
            ->afterCreating(function (User $user) use ($tenant, $role): void {
                app(TenantContext::class)->run($tenant, fn () => $user->assignRole($role->value));
            });
    }

    /**
     * Usuário da equipe da plataforma, com acesso à Central.
     */
    public function centralAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_central_admin' => true,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

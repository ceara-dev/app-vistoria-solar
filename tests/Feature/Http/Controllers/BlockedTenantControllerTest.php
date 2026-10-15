<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('explains why the current tenant is blocked', function () {
    $tenant = Tenant::factory()->suspended()->create(['name' => 'Solar Suspensa']);
    $user = User::factory()->forTenant($tenant)->create();

    $this->actingAs($user)
        ->get(route('tenants.blocked'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/TenantBlocked')
            ->where('tenantName', 'Solar Suspensa')
            ->where('reason', 'O acesso desta empresa está suspenso.')
            ->where('hasOtherTenants', false));
});

it('sends the user back to the dashboard when the tenant allows access', function () {
    $user = User::factory()->forTenant(Tenant::factory()->create())->create();

    $this->actingAs($user)
        ->get(route('tenants.blocked'))
        ->assertRedirect(route('tenant.dashboard'));
});

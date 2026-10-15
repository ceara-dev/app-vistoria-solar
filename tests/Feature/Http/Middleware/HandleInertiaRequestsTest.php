<?php

use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\MemberRepositoryInterface;
use App\Tenancy\TenantRole;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

it('shares only the public fields of the logged in user', function () {
    $user = User::factory()->forTenant(Tenant::factory()->create())->create();

    $this->actingAs($user)
        ->get(route('tenant.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]));
});

it('shares the current tenant and the active tenants of the user', function () {
    $currentTenant = Tenant::factory()->create(['name' => 'Atual']);
    $otherTenant = Tenant::factory()->create(['name' => 'Outra']);
    $formerTenant = Tenant::factory()->create(['name' => 'Antiga']);
    $user = User::factory()
        ->forTenant($currentTenant)
        ->hasAttached($otherTenant, ['is_active' => true], 'tenants')
        ->hasAttached($formerTenant, ['is_active' => false], 'tenants')
        ->create();

    $this->actingAs($user)
        ->get(route('tenant.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tenant', ['id' => $currentTenant->id, 'name' => 'Atual'])
            ->where('tenants', [
                ['id' => $currentTenant->id, 'name' => 'Atual'],
                ['id' => $otherTenant->id, 'name' => 'Outra'],
            ]));
});

it('shares the permissions of the role in the current tenant only', function () {
    $inspectorTenant = Tenant::factory()->create();
    $adminTenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($inspectorTenant, TenantRole::Inspector)->create();
    $members = app(MemberRepositoryInterface::class);
    $members->activate($adminTenant, $user);
    $members->assignRole($adminTenant, $user, TenantRole::Admin);

    $this->actingAs($user)
        ->get(route('tenant.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('can', ['customers.view', 'trips.record']));

    $user->forceFill(['current_tenant_id' => $adminTenant->id])->save();

    $this->actingAs($user)
        ->get(route('tenant.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where(
            'can',
            fn (Collection $permissions): bool => $permissions->sort()->values()->all() === ['customers.manage', 'customers.view', 'members.manage', 'members.view', 'trips.record'],
        ));
});

it('shares no tenant data with guests', function () {
    $this->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.user', null)
            ->where('tenant', null)
            ->where('tenants', [])
            ->where('can', []));
});

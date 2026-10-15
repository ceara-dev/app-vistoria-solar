<?php

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantRole;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    it('lists only the active tenants of the user', function () {
        $activeTenant = Tenant::factory()->create(['name' => 'Ativa']);
        $inactiveTenant = Tenant::factory()->create(['name' => 'Inativa']);
        Tenant::factory()->create(['name' => 'De outra pessoa']);
        $user = User::factory()
            ->hasAttached($activeTenant, ['is_active' => true], 'tenants')
            ->hasAttached($inactiveTenant, ['is_active' => false], 'tenants')
            ->create();

        $this->actingAs($user)
            ->get(route('tenants.select'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/SelectTenant')
                ->has('tenants.data', 1)
                ->where('tenants.data.0.name', 'Ativa'));
    });
});

describe('update', function () {
    it('switches to another tenant of the user', function () {
        $currentTenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();
        $user = User::factory()
            ->forTenant($currentTenant)
            ->hasAttached($otherTenant, ['is_active' => true], 'tenants')
            ->create();

        $this->actingAs($user)
            ->put(route('current-tenant.update'), ['tenant_id' => $otherTenant->id])
            ->assertRedirect(route('tenant.dashboard'));

        expect($user->fresh()->current_tenant_id)->toBe($otherTenant->id);
    });

    it('rejects a tenant the user does not belong to', function () {
        $currentTenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($currentTenant)->create();

        $this->actingAs($user)
            ->put(route('current-tenant.update'), ['tenant_id' => Tenant::factory()->create()->id])
            ->assertSessionHasErrors(['tenant_id' => 'O valor selecionado é inválido']);

        expect($user->fresh()->current_tenant_id)->toBe($currentTenant->id);
    });

    it('rejects a tenant whose membership was deactivated', function () {
        $currentTenant = Tenant::factory()->create();
        $formerTenant = Tenant::factory()->create();
        $user = User::factory()
            ->forTenant($currentTenant)
            ->hasAttached($formerTenant, ['is_active' => false], 'tenants')
            ->create();

        $this->actingAs($user)
            ->put(route('current-tenant.update'), ['tenant_id' => $formerTenant->id])
            ->assertSessionHasErrors('tenant_id');
    });
});

describe('store', function () {
    it('creates a tenant owned by the user and makes it current', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tenants.store'), ['company_name' => 'Nova Solar'])
            ->assertRedirect(route('tenant.dashboard'));

        $tenant = $user->fresh()->currentTenant;

        expect($tenant->name)->toBe('Nova Solar')
            ->and(tenantRoleOf($tenant, $user))->toBe(TenantRole::Owner);
    });

    it('rejects a company name above 255 characters', function () {
        $this->actingAs(User::factory()->create())
            ->post(route('tenants.store'), ['company_name' => str_repeat('a', 256)])
            ->assertSessionHasErrors(['company_name' => 'Máximo de 255 caracteres']);
    });
});

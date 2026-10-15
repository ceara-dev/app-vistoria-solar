<?php

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantIdResolver;
use OwenIt\Auditing\Contracts\Auditable;

it('uses the tenant of the audited record over the current tenant', function () {
    $recordTenant = Tenant::factory()->create();
    $invitation = TenantInvitation::factory()->for($recordTenant)->make();
    app(TenantContext::class)->set(Tenant::factory()->create());

    expect(TenantIdResolver::resolve($invitation))->toBe($recordTenant->id);
});

it('uses the tenant itself when the audited record is a tenant', function () {
    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set(Tenant::factory()->create());

    expect(TenantIdResolver::resolve($tenant))->toBe($tenant->id);
});

it('falls back to the current tenant for records without one', function () {
    $currentTenant = Tenant::factory()->create();
    app(TenantContext::class)->set($currentTenant);

    expect(TenantIdResolver::resolve(Mockery::mock(Auditable::class)))->toBe($currentTenant->id);
});

it('leaves the tenant empty for records without one outside a tenant', function () {
    expect(TenantIdResolver::resolve(Mockery::mock(Auditable::class)))->toBeNull();
});

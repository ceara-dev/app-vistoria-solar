<?php

use App\Models\Audit;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantNotResolvedException;

it('only lists the history of the current tenant', function () {
    $currentTenant = Tenant::factory()->create();
    Tenant::factory()->create();

    app(TenantContext::class)->set($currentTenant);

    expect(Audit::pluck('tenant_id')->unique()->all())->toBe([$currentTenant->id]);
});

it('throws when the history is queried without a current tenant', function () {
    Audit::query()->get();
})->throws(TenantNotResolvedException::class);

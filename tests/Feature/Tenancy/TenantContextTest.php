<?php

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Facade;
use Tests\Fixtures\TenantNote;

it('restores the previous tenant after running a callback in another tenant', function () {
    $previousTenant = Tenant::factory()->create();
    $otherTenant = Tenant::factory()->create();
    $tenantContext = app(TenantContext::class);
    $tenantContext->set($previousTenant);

    $tenantIdInsideCallback = $tenantContext->run($otherTenant, fn () => $tenantContext->id());

    expect($tenantIdInsideCallback)->toBe($otherTenant->id)
        ->and($tenantContext->id())->toBe($previousTenant->id);
});

it('restores the previous tenant when the callback throws', function () {
    $previousTenant = Tenant::factory()->create();
    $otherTenant = Tenant::factory()->create();
    $tenantContext = app(TenantContext::class);
    $tenantContext->set($previousTenant);

    try {
        $tenantContext->run($otherTenant, fn () => throw new RuntimeException('Falha no callback'));
    } catch (RuntimeException) {
        //
    }

    expect($tenantContext->id())->toBe($previousTenant->id);
});

it('shares the current tenant with the log context and the permission team', function () {
    $tenant = Tenant::factory()->create();

    app(TenantContext::class)->set($tenant);

    expect(Context::get('tenant_id'))->toBe($tenant->id)
        ->and(getPermissionsTeamId())->toBe($tenant->id);
});

it('restores the tenant of a queued job in a fresh worker scope', function () {
    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($tenant);
    $jobContext = Context::dehydrate();

    // O worker descarta as instâncias scoped e os facades resolvidos antes de cada job.
    app()->forgetScopedInstances();
    Facade::clearResolvedInstances();
    Context::hydrate($jobContext);

    expect(app(TenantContext::class)->id())->toBe($tenant->id);
});

it('clears the tenant of the previous job when a queued job carries no tenant', function () {
    $previousJobTenant = Tenant::factory()->create();
    app(TenantContext::class)->set($previousJobTenant);

    Context::hydrate(null);

    expect(app(TenantContext::class)->id())->toBeNull()
        ->and(getPermissionsTeamId())->toBeNull();
});

it('tells whether a record belongs to the current tenant', function () {
    TenantNote::createTable();
    $currentTenant = Tenant::factory()->create();
    $ownNote = TenantNote::forceCreate(['tenant_id' => $currentTenant->id, 'title' => 'Própria']);
    $otherNote = TenantNote::forceCreate(['tenant_id' => Tenant::factory()->create()->id, 'title' => 'Alheia']);
    $tenantContext = app(TenantContext::class);

    $ownedWithoutTenant = $tenantContext->owns($ownNote);
    $tenantContext->set($currentTenant);

    expect($ownedWithoutTenant)->toBeFalse()
        ->and($tenantContext->owns($ownNote))->toBeTrue()
        ->and($tenantContext->owns($otherNote))->toBeFalse();
});

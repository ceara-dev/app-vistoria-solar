<?php

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;
use Tests\Fixtures\TenantNote;

beforeEach(function () {
    TenantNote::createTable();

    // As telas de escolha e de bloqueio chegam na fase 2. Aqui só os destinos importam.
    Route::get('/_test/select', fn () => 'select')->name('tenants.select');
    Route::get('/_test/blocked', fn () => 'blocked')->name('tenants.blocked');

    Route::middleware(['web', 'auth', 'tenant'])->group(function () {
        Route::get('/_test/current-tenant', fn (TenantContext $tenantContext) => (string) $tenantContext->id());
        Route::get('/_test/notes/{note}', fn (TenantNote $note) => $note->title);
    });

    Route::getRoutes()->refreshNameLookups();
});

it('sets the current tenant of the member for the request', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();

    $this->actingAs($user)
        ->get('/_test/current-tenant')
        ->assertOk()
        ->assertContent((string) $tenant->id);
});

it('adopts the only active tenant when the member has no current tenant', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->hasAttached($tenant, ['is_active' => true], 'tenants')->create();

    $this->actingAs($user)
        ->get('/_test/current-tenant')
        ->assertContent((string) $tenant->id);

    expect($user->fresh()->current_tenant_id)->toBe($tenant->id);
});

it('keeps the current tenant when the member belongs to several tenants', function () {
    $currentTenant = Tenant::factory()->create();
    $otherTenant = Tenant::factory()->create();
    $user = User::factory()
        ->forTenant($currentTenant)
        ->hasAttached($otherTenant, ['is_active' => true], 'tenants')
        ->create();

    $this->actingAs($user)
        ->get('/_test/current-tenant')
        ->assertContent((string) $currentTenant->id);
});

it('redirects to tenant selection when the member has several tenants and none is current', function () {
    $user = User::factory()
        ->hasAttached(Tenant::factory()->count(2), ['is_active' => true], 'tenants')
        ->create();

    $this->actingAs($user)
        ->get('/_test/current-tenant')
        ->assertRedirect(route('tenants.select'));
});

it('leaves the current tenant when the membership was deactivated', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $user->tenants()->updateExistingPivot($tenant, ['is_active' => false]);

    $this->actingAs($user)
        ->get('/_test/current-tenant')
        ->assertRedirect(route('tenants.select'));

    expect($user->fresh()->current_tenant_id)->toBeNull();
});

it('redirects to the blocked page when the tenant does not allow access', function (string $state) {
    $tenant = Tenant::factory()->{$state}()->create();
    $user = User::factory()->forTenant($tenant)->create();

    $this->actingAs($user)
        ->get('/_test/current-tenant')
        ->assertRedirect(route('tenants.blocked'));
})->with([
    'suspended' => 'suspended',
    'canceled' => 'canceled',
    'trial expired' => 'expiredTrial',
]);

it('resolves a route record of the current tenant', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $note = TenantNote::forceCreate(['tenant_id' => $tenant->id, 'title' => 'Nota da empresa']);

    $this->actingAs($user)
        ->get("/_test/notes/{$note->id}")
        ->assertOk()
        ->assertContent('Nota da empresa');
});

it('returns 404 for a route record of another tenant', function () {
    $user = User::factory()->forTenant(Tenant::factory()->create())->create();
    $otherTenantNote = TenantNote::forceCreate([
        'tenant_id' => Tenant::factory()->create()->id,
        'title' => 'Nota de outra empresa',
    ]);

    $this->actingAs($user)
        ->get("/_test/notes/{$otherTenantNote->id}")
        ->assertNotFound();
});

<?php

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantNotResolvedException;
use Tests\Fixtures\TenantNote;

beforeEach(function () {
    TenantNote::createTable();
});

it('throws when a tenant model is queried without a current tenant', function () {
    TenantNote::query()->get();
})->throws(TenantNotResolvedException::class);

it('only returns records of the current tenant', function () {
    $currentTenant = Tenant::factory()->create();
    $otherTenant = Tenant::factory()->create();
    $ownNote = TenantNote::forceCreate(['tenant_id' => $currentTenant->id, 'title' => 'Própria']);
    TenantNote::forceCreate(['tenant_id' => $otherTenant->id, 'title' => 'De outra empresa']);

    app(TenantContext::class)->set($currentTenant);

    expect(TenantNote::pluck('id')->all())->toBe([$ownNote->id]);
});

it('fills tenant_id with the current tenant on create', function () {
    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($tenant);

    $note = TenantNote::create(['title' => 'Nota']);

    expect($note->tenant_id)->toBe($tenant->id);
});

it('ignores a tenant_id sent through mass assignment', function () {
    $currentTenant = Tenant::factory()->create();
    $otherTenant = Tenant::factory()->create();
    app(TenantContext::class)->set($currentTenant);

    $note = TenantNote::create(['title' => 'Nota', 'tenant_id' => $otherTenant->id]);

    expect($note->tenant_id)->toBe($currentTenant->id);
});

it('rejects creating a record of another tenant inside the current tenant', function () {
    $currentTenant = Tenant::factory()->create();
    $otherTenant = Tenant::factory()->create();
    app(TenantContext::class)->set($currentTenant);

    TenantNote::forceCreate(['tenant_id' => $otherTenant->id, 'title' => 'Nota']);
})->throws(LogicException::class, 'Registro de outra empresa criado dentro do contexto atual.');

it('throws when a record is created without tenant_id and without a current tenant', function () {
    TenantNote::create(['title' => 'Nota']);
})->throws(TenantNotResolvedException::class);

it('rejects moving a record to another tenant', function () {
    $currentTenant = Tenant::factory()->create();
    $otherTenant = Tenant::factory()->create();
    app(TenantContext::class)->set($currentTenant);
    $note = TenantNote::create(['title' => 'Nota']);

    $note->tenant_id = $otherTenant->id;
    $note->save();
})->throws(LogicException::class, 'A empresa de um registro não pode ser alterada.');

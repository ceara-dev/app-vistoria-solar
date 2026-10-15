<?php

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantRule;
use Illuminate\Support\Facades\Validator;
use Tests\Fixtures\TenantNote;

beforeEach(function () {
    TenantNote::createTable();
});

it('accepts an id that exists in the current tenant', function () {
    $tenant = Tenant::factory()->create();
    $note = TenantNote::forceCreate(['tenant_id' => $tenant->id, 'title' => 'Nota']);
    app(TenantContext::class)->set($tenant);

    $validator = Validator::make(['note_id' => $note->id], ['note_id' => TenantRule::exists('tenant_notes')]);

    expect($validator->passes())->toBeTrue();
});

it('rejects an id that exists only in another tenant', function () {
    $otherTenantNote = TenantNote::forceCreate(['tenant_id' => Tenant::factory()->create()->id, 'title' => 'Nota']);
    app(TenantContext::class)->set(Tenant::factory()->create());

    $validator = Validator::make(['note_id' => $otherTenantNote->id], ['note_id' => TenantRule::exists('tenant_notes')]);

    expect($validator->passes())->toBeFalse();
});

it('rejects a value already used in the current tenant', function () {
    $tenant = Tenant::factory()->create();
    TenantNote::forceCreate(['tenant_id' => $tenant->id, 'title' => 'Repetida']);
    app(TenantContext::class)->set($tenant);

    $validator = Validator::make(['title' => 'Repetida'], ['title' => TenantRule::unique('tenant_notes', 'title')]);

    expect($validator->passes())->toBeFalse();
});

it('accepts a value used only by another tenant', function () {
    TenantNote::forceCreate(['tenant_id' => Tenant::factory()->create()->id, 'title' => 'Repetida']);
    app(TenantContext::class)->set(Tenant::factory()->create());

    $validator = Validator::make(['title' => 'Repetida'], ['title' => TenantRule::unique('tenant_notes', 'title')]);

    expect($validator->passes())->toBeTrue();
});

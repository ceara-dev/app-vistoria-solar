<?php

use App\Models\PersonalAccessToken;
use App\Models\Tenant;
use App\Models\User;

it('works in the tenant of the token, not in the current tenant of the web', function () {
    $tokenTenant = Tenant::factory()->create(['name' => 'Do token']);
    $webTenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($webTenant)->hasAttached($tokenTenant, ['is_active' => true], 'tenants')->create();

    $this->withToken(apiTokenFor($user, $tokenTenant))
        ->getJson(route('api.v1.me'))
        ->assertOk()
        ->assertJsonPath('data.tenant', ['id' => $tokenTenant->id, 'name' => 'Do token'])
        ->assertJsonMissingPath('data.token');
});

it('ends the session and revokes the token when the membership was deactivated', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $token = apiTokenFor($user, $tenant);
    $user->tenants()->updateExistingPivot($tenant, ['is_active' => false]);

    $this->withToken($token)
        ->getJson(route('api.v1.me'))
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Sua sessão nesta empresa foi encerrada.');

    expect(PersonalAccessToken::findToken($token))->toBeNull();
});

it('keeps the token but blocks the request while the tenant has no access', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $token = apiTokenFor($user, $tenant);
    $tenant->forceFill(['status' => 'suspended'])->save();

    $this->withToken($token)->getJson(route('api.v1.me'))->assertStatus(423);

    expect(PersonalAccessToken::findToken($token))->not->toBeNull();
});

it('rejects a token without a tenant', function () {
    $user = User::factory()->forTenant(Tenant::factory()->create())->create();
    $token = $user->createToken('Sem empresa')->plainTextToken;

    $this->withToken($token)->getJson(route('api.v1.me'))->assertUnauthorized();
});

it('rejects requests without a token', function () {
    $this->getJson(route('api.v1.me'))->assertUnauthorized();
});

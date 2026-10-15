<?php

use App\Models\PersonalAccessToken;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantRole;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function loginPayload(User $user, array $overrides = []): array
{
    return ['email' => $user->email, 'password' => 'password', 'device_name' => 'Celular da Ana', ...$overrides];
}

describe('store', function () {
    it('issues a token bound to the only active tenant of the user', function () {
        $tenant = Tenant::factory()->create(['name' => 'Solar Ana']);
        $user = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();

        $response = $this->postJson(route('api.v1.auth.token.store'), loginPayload($user));

        $response->assertCreated()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.tenant', ['id' => $tenant->id, 'name' => 'Solar Ana'])
            ->assertJsonPath('data.permissions', ['customers.view', 'trips.record']);

        $token = PersonalAccessToken::findToken($response->json('data.token'));

        expect($token->tenant_id)->toBe($tenant->id)
            ->and($token->name)->toBe('Celular da Ana')
            ->and($token->expires_at->isSameDay(now()->addDays(30)))->toBeTrue();
    });

    it('asks which tenant when the user belongs to several', function () {
        $tenantB = Tenant::factory()->create(['name' => 'B Solar']);
        $tenantA = Tenant::factory()->create(['name' => 'A Solar']);
        $user = User::factory()->forTenant($tenantB)->hasAttached($tenantA, ['is_active' => true], 'tenants')->create();

        $this->postJson(route('api.v1.auth.token.store'), loginPayload($user))
            ->assertStatus(409)
            ->assertJsonPath('tenants', [
                ['id' => $tenantA->id, 'name' => 'A Solar'],
                ['id' => $tenantB->id, 'name' => 'B Solar'],
            ]);

        expect(PersonalAccessToken::count())->toBe(0);
    });

    it('issues the token for the chosen tenant', function () {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenantA)->hasAttached($tenantB, ['is_active' => true], 'tenants')->create();

        $this->postJson(route('api.v1.auth.token.store'), loginPayload($user, ['tenant_id' => $tenantB->id]))
            ->assertCreated()
            ->assertJsonPath('data.tenant.id', $tenantB->id);
    });

    it('refuses a tenant the user is not an active member of', function (Closure $makeTenant) {
        $user = User::factory()->forTenant(Tenant::factory()->create())->create();
        $tenant = $makeTenant($user);

        $this->postJson(route('api.v1.auth.token.store'), loginPayload($user, ['tenant_id' => $tenant->id]))
            ->assertForbidden()
            ->assertJsonPath('message', 'Você não tem acesso a esta empresa.');
    })->with([
        'stranger' => [fn (User $user) => Tenant::factory()->create()],
        'former member' => [fn (User $user) => tap(Tenant::factory()->create(), fn (Tenant $tenant) => $user->tenants()->attach($tenant, ['is_active' => false]))],
    ]);

    it('refuses a user without any active tenant', function () {
        $user = User::factory()->create();

        $this->postJson(route('api.v1.auth.token.store'), loginPayload($user))
            ->assertForbidden()
            ->assertJsonPath('message', 'Você não participa de nenhuma empresa ativa.');
    });

    it('rejects a wrong password', function () {
        $user = User::factory()->forTenant(Tenant::factory()->create())->create();

        $this->postJson(route('api.v1.auth.token.store'), loginPayload($user, ['password' => 'errada']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email' => 'E-mail ou senha incorretos']);
    });

    it('refuses a tenant without access, with the reason', function () {
        $user = User::factory()->forTenant(Tenant::factory()->suspended()->create())->create();

        $this->postJson(route('api.v1.auth.token.store'), loginPayload($user))
            ->assertStatus(423)
            ->assertJsonPath('message', 'O acesso desta empresa está suspenso.');

        expect(PersonalAccessToken::count())->toBe(0);
    });

    it('limits login attempts per email', function () {
        $user = User::factory()->forTenant(Tenant::factory()->create())->create();

        foreach (range(1, 5) as $attempt) {
            $this->postJson(route('api.v1.auth.token.store'), loginPayload($user, ['password' => 'errada']));
        }

        $this->postJson(route('api.v1.auth.token.store'), loginPayload($user))->assertTooManyRequests();
    });
});

describe('destroy', function () {
    it('revokes only the token in use', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $token = apiTokenFor($user, $tenant);
        $otherDeviceToken = apiTokenFor($user, $tenant);

        $this->withToken($token)->deleteJson(route('api.v1.auth.token.destroy'))->assertNoContent();

        expect(PersonalAccessToken::findToken($token))->toBeNull()
            ->and(PersonalAccessToken::findToken($otherDeviceToken))->not->toBeNull();
    });
});

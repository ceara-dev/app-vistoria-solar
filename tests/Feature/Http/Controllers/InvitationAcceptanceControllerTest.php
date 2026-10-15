<?php

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Tenancy\TenantRole;
use Inertia\Testing\AssertableInertia as Assert;

describe('show', function () {
    it('returns 404 for an unusable invitation link', function (Closure $makeInvitation) {
        $makeInvitation();

        $this->get(route('invitations.show', 'token-do-convite'))->assertNotFound();
    })->with([
        'unknown token' => [fn () => TenantInvitation::factory()->create()],
        'expired' => [fn () => TenantInvitation::factory()->withToken('token-do-convite')->expired()->create()],
        'already accepted' => [fn () => TenantInvitation::factory()->withToken('token-do-convite')->accepted()->create()],
    ]);

    it('shows the account form to a guest without an account', function () {
        $tenant = Tenant::factory()->create(['name' => 'Solar Ana']);
        TenantInvitation::factory()->for($tenant)->withToken('token-do-convite')->create([
            'email' => 'bruno@example.com',
            'role' => TenantRole::Manager,
        ]);

        $this->get(route('invitations.show', 'token-do-convite'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/AcceptInvitation')
                ->where('mode', 'create_account')
                ->where('invitation.email', 'bruno@example.com')
                ->where('invitation.tenant_name', 'Solar Ana')
                ->where('invitation.role_label', 'Gestor'));
    });

    it('asks a guest who already has an account to log in and come back', function () {
        User::factory()->create(['email' => 'bruno@example.com']);
        TenantInvitation::factory()->withToken('token-do-convite')->create(['email' => 'bruno@example.com']);

        $this->get(route('invitations.show', 'token-do-convite'))
            ->assertInertia(fn (Assert $page) => $page->where('mode', 'login'))
            ->assertSessionHas('url.intended', route('invitations.show', 'token-do-convite'));
    });

    it('warns a user logged in with another email', function () {
        TenantInvitation::factory()->withToken('token-do-convite')->create(['email' => 'bruno@example.com']);

        $this->actingAs(User::factory()->create(['email' => 'carla@example.com']))
            ->get(route('invitations.show', 'token-do-convite'))
            ->assertInertia(fn (Assert $page) => $page->where('mode', 'wrong_account'));
    });
});

describe('store', function () {
    it('creates the account of a guest and joins the tenant with the invited role', function () {
        $tenant = Tenant::factory()->create();
        $invitation = TenantInvitation::factory()->for($tenant)->withToken('token-do-convite')->create([
            'email' => 'bruno@example.com',
            'role' => TenantRole::Inspector,
        ]);

        $this->post(route('invitations.accept', 'token-do-convite'), [
            'name' => 'Bruno Lima',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('tenant.dashboard'));

        $user = User::where('email', 'bruno@example.com')->sole();

        $this->assertAuthenticatedAs($user);
        expect($user->hasVerifiedEmail())->toBeTrue()
            ->and($user->current_tenant_id)->toBe($tenant->id)
            ->and(tenantRoleOf($tenant, $user))->toBe(TenantRole::Inspector)
            ->and($invitation->fresh()->accepted_at)->not->toBeNull();
    });

    it('requires a name and password from a guest without an account', function () {
        TenantInvitation::factory()->withToken('token-do-convite')->create();

        $this->post(route('invitations.accept', 'token-do-convite'), [])
            ->assertSessionHasErrors(['name' => 'Este campo é obrigatório', 'password' => 'Este campo é obrigatório']);
    });

    it('adds a logged in user to the tenant and makes it current', function () {
        $currentTenant = Tenant::factory()->create();
        $invitingTenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($currentTenant)->create(['email' => 'bruno@example.com']);
        TenantInvitation::factory()->for($invitingTenant)->withToken('token-do-convite')->create([
            'email' => 'bruno@example.com',
            'role' => TenantRole::Admin,
        ]);

        $this->actingAs($user)
            ->post(route('invitations.accept', 'token-do-convite'))
            ->assertRedirect(route('tenant.dashboard'));

        $user->refresh();

        expect($user->current_tenant_id)->toBe($invitingTenant->id)
            ->and($user->isActiveMemberOf($currentTenant))->toBeTrue()
            ->and(tenantRoleOf($invitingTenant, $user))->toBe(TenantRole::Admin);
    });

    it('restores the access of a former member', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->hasAttached($tenant, ['is_active' => false], 'tenants')->create(['email' => 'bruno@example.com']);
        TenantInvitation::factory()->for($tenant)->withToken('token-do-convite')->create(['email' => 'bruno@example.com']);

        $this->actingAs($user)->post(route('invitations.accept', 'token-do-convite'));

        expect($user->isActiveMemberOf($tenant))->toBeTrue();
    });

    it('rejects a user logged in with another email', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['email' => 'carla@example.com']);
        TenantInvitation::factory()->for($tenant)->withToken('token-do-convite')->create(['email' => 'bruno@example.com']);

        $this->actingAs($user)
            ->post(route('invitations.accept', 'token-do-convite'))
            ->assertSessionHasErrors(['invitation' => 'Este convite foi enviado para outro e-mail.']);

        expect($user->isActiveMemberOf($tenant))->toBeFalse();
    });

    it('rejects creating a second account for an email that already has one', function () {
        $user = User::factory()->create(['email' => 'bruno@example.com']);
        TenantInvitation::factory()->withToken('token-do-convite')->create(['email' => 'bruno@example.com']);

        $this->post(route('invitations.accept', 'token-do-convite'), [
            'name' => 'Outra pessoa',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('invitation');

        $this->assertGuest();
        expect(User::where('email', 'bruno@example.com')->count())->toBe(1)
            ->and($user->fresh()->name)->not->toBe('Outra pessoa');
    });
});

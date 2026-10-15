<?php

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

/**
 * Convites de uma empresa, lidos fora de qualquer requisição.
 *
 * @return Collection<int, TenantInvitation>
 */
function invitationsOf(Tenant $tenant)
{
    return app(TenantContext::class)->run($tenant, fn () => TenantInvitation::all());
}

describe('store', function () {
    it('creates the invitation and emails a link whose token matches it', function () {
        Notification::fake();
        $tenant = Tenant::factory()->create(['name' => 'Solar Ana']);
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();

        $this->actingAs($admin)
            ->post(route('tenant.invitations.store'), ['email' => 'Bruno@Example.com', 'role' => TenantRole::Inspector->value])
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        $invitation = invitationsOf($tenant)->sole();

        expect($invitation->email)->toBe('bruno@example.com')
            ->and($invitation->role)->toBe(TenantRole::Inspector)
            ->and($invitation->expires_at->isFuture())->toBeTrue();

        Notification::assertSentOnDemand(
            TenantInvitationNotification::class,
            function (TenantInvitationNotification $notification, array $channels, AnonymousNotifiable $notifiable) use ($invitation): bool {
                return $notifiable->routes['mail'] === 'bruno@example.com'
                    && $notification->tenantName === 'Solar Ana'
                    && hash('sha256', $notification->token) === $invitation->token_hash;
            },
        );
    });

    it('renews the pending invitation of the same email instead of duplicating it', function () {
        Notification::fake();
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $this->actingAs($admin)->post(route('tenant.invitations.store'), ['email' => 'bruno@example.com', 'role' => 'vistoriador']);
        $firstTokenHash = invitationsOf($tenant)->sole()->token_hash;

        $this->actingAs($admin)->post(route('tenant.invitations.store'), ['email' => 'bruno@example.com', 'role' => 'gestor']);

        $invitation = invitationsOf($tenant)->sole();

        expect($invitation->role)->toBe(TenantRole::Manager)
            ->and($invitation->token_hash)->not->toBe($firstTokenHash);
    });

    it('rejects inviting someone who is already an active member', function () {
        Notification::fake();
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $member = User::factory()->forTenant($tenant)->create();

        $this->actingAs($admin)
            ->post(route('tenant.invitations.store'), ['email' => $member->email, 'role' => 'vistoriador'])
            ->assertSessionHasErrors(['email' => 'Esta pessoa já faz parte da empresa.']);

        Notification::assertNothingSent();
    });

    it('rejects an administrator inviting an owner', function () {
        Notification::fake();
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();

        $this->actingAs($admin)
            ->post(route('tenant.invitations.store'), ['email' => 'bruno@example.com', 'role' => TenantRole::Owner->value])
            ->assertSessionHasErrors(['role' => 'Só um proprietário pode convidar proprietários.']);
    });

    it('rejects an invalid email with the contract message', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();

        $this->actingAs($admin)
            ->post(route('tenant.invitations.store'), ['email' => 'bruno@', 'role' => 'vistoriador'])
            ->assertSessionHasErrors(['email' => 'E-mail inválido']);
    });

    it('forbids members without permission to manage members', function () {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant, TenantRole::Manager)->create();

        $this->actingAs($manager)
            ->post(route('tenant.invitations.store'), ['email' => 'bruno@example.com', 'role' => 'vistoriador'])
            ->assertForbidden();
    });
});

describe('destroy', function () {
    it('cancels a pending invitation', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $invitation = TenantInvitation::factory()->for($tenant)->create();

        $this->actingAs($admin)
            ->delete(route('tenant.invitations.destroy', $invitation))
            ->assertRedirect();

        expect(invitationsOf($tenant))->toBeEmpty();
    });

    it('returns 404 for an invitation of another tenant', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $otherTenantInvitation = TenantInvitation::factory()->create();

        $this->actingAs($admin)
            ->delete(route('tenant.invitations.destroy', $otherTenantInvitation))
            ->assertNotFound();
    });
});

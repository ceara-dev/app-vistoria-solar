<?php

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Tenancy\TenantRole;
use Illuminate\Support\Facades\Notification;

it('records the invitation in the history of its tenant without the token', function () {
    Notification::fake();
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();

    $this->actingAs($admin)
        ->post(route('tenant.invitations.store'), ['email' => 'bruno@example.com', 'role' => 'gestor']);

    $audit = auditsOf($tenant, 'created')->firstWhere('auditable_type', TenantInvitation::class);

    expect($audit->user_id)->toBe($admin->id)
        ->and($audit->new_values)->toMatchArray(['email' => 'bruno@example.com', 'role' => 'gestor'])
        ->and($audit->new_values)->not->toHaveKey('token_hash');
});

it('records the acceptance in the history of its tenant, outside any tenant context', function () {
    $tenant = Tenant::factory()->create();
    $invitation = TenantInvitation::factory()->for($tenant)->withToken('token-do-convite')->create();

    $this->post(route('invitations.accept', 'token-do-convite'), [
        'name' => 'Bruno Lima',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $acceptance = auditsOf($tenant, 'updated')
        ->where('auditable_type', TenantInvitation::class)
        ->firstWhere('auditable_id', $invitation->id);

    expect($acceptance->new_values)->toHaveKey('accepted_at')
        ->and($acceptance->new_values)->not->toHaveKey('token_hash');
});

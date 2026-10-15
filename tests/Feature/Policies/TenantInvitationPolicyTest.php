<?php

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Tenancy\TenantRole;

it('lets only the roles that manage members create and cancel invitations', function (TenantRole $role, bool $isAllowed) {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant, $role)->create();
    $invitation = TenantInvitation::factory()->for($tenant)->create();

    actAsMemberIn($user, $tenant);

    expect($user->can('create', TenantInvitation::class))->toBe($isAllowed)
        ->and($user->can('delete', $invitation))->toBe($isAllowed);
})->with([
    'proprietário' => [TenantRole::Owner, true],
    'administrador' => [TenantRole::Admin, true],
    'gestor' => [TenantRole::Manager, false],
    'vistoriador' => [TenantRole::Inspector, false],
]);

it('denies cancelling an invitation of another tenant, even to an owner', function () {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->forTenant($tenant, TenantRole::Owner)->create();
    $otherTenantInvitation = TenantInvitation::factory()->create();

    actAsMemberIn($owner, $tenant);

    expect($owner->can('delete', $otherTenantInvitation))->toBeFalse();
});

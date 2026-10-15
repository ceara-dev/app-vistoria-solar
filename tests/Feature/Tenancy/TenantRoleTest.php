<?php

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantPermission;
use App\Tenancy\TenantRole;

/**
 * @return array<string, bool>
 */
function grantedPermissions(User $user): array
{
    return collect(TenantPermission::cases())
        ->mapWithKeys(fn (TenantPermission $permission): array => [$permission->value => $user->can($permission->value)])
        ->all();
}

it('grants each role exactly its permissions in the tenant', function (TenantRole $role, array $expectedPermissions) {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant, $role)->create();

    actAsMemberIn($user, $tenant);

    expect(grantedPermissions($user))->toBe($expectedPermissions);
})->with([
    'proprietário' => [TenantRole::Owner, ['members.view' => true, 'members.manage' => true, 'customers.view' => true, 'customers.manage' => true, 'trips.record' => true]],
    'administrador' => [TenantRole::Admin, ['members.view' => true, 'members.manage' => true, 'customers.view' => true, 'customers.manage' => true, 'trips.record' => true]],
    'gestor' => [TenantRole::Manager, ['members.view' => true, 'members.manage' => false, 'customers.view' => true, 'customers.manage' => true, 'trips.record' => true]],
    'vistoriador' => [TenantRole::Inspector, ['members.view' => false, 'members.manage' => false, 'customers.view' => true, 'customers.manage' => false, 'trips.record' => true]],
]);

it('does not carry a role from one tenant into another', function () {
    $adminTenant = Tenant::factory()->create();
    $inspectorTenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($adminTenant, TenantRole::Admin)->create();
    addMembership($inspectorTenant, $user, TenantRole::Inspector);

    actAsMemberIn($user, $inspectorTenant);
    $permissionsInInspectorTenant = grantedPermissions($user);

    actAsMemberIn($user, $adminTenant);
    $permissionsInAdminTenant = grantedPermissions($user);

    expect($permissionsInInspectorTenant)->toBe(['members.view' => false, 'members.manage' => false, 'customers.view' => true, 'customers.manage' => false, 'trips.record' => true])
        ->and($permissionsInAdminTenant)->toBe(['members.view' => true, 'members.manage' => true, 'customers.view' => true, 'customers.manage' => true, 'trips.record' => true]);
});

it('grants nothing without a current tenant', function () {
    $user = User::factory()->forTenant(Tenant::factory()->create(), TenantRole::Owner)->create();

    app(TenantContext::class)->set(null);
    $user->unsetRelation('roles')->unsetRelation('permissions');

    expect(grantedPermissions($user))->toBe(['members.view' => false, 'members.manage' => false, 'customers.view' => false, 'customers.manage' => false, 'trips.record' => false]);
});

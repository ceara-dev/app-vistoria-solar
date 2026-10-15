<?php

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantRole;

/**
 * @return array<string, bool>
 */
function customerAbilities(User $user, Customer $customer): array
{
    return [
        'viewAny' => $user->can('viewAny', Customer::class),
        'view' => $user->can('view', $customer),
        'create' => $user->can('create', Customer::class),
        'update' => $user->can('update', $customer),
        'delete' => $user->can('delete', $customer),
    ];
}

it('grants each role its abilities over the customers of the tenant', function (TenantRole $role, array $expectedAbilities) {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant, $role)->create();
    $customer = Customer::factory()->for($tenant)->create();

    actAsMemberIn($user, $tenant);

    expect(customerAbilities($user, $customer))->toBe($expectedAbilities);
})->with([
    'proprietário' => [TenantRole::Owner, ['viewAny' => true, 'view' => true, 'create' => true, 'update' => true, 'delete' => true]],
    'administrador' => [TenantRole::Admin, ['viewAny' => true, 'view' => true, 'create' => true, 'update' => true, 'delete' => true]],
    'gestor' => [TenantRole::Manager, ['viewAny' => true, 'view' => true, 'create' => true, 'update' => true, 'delete' => true]],
    'vistoriador' => [TenantRole::Inspector, ['viewAny' => true, 'view' => true, 'create' => false, 'update' => false, 'delete' => false]],
]);

it('denies every record ability over a customer of another tenant, even to an owner', function () {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->forTenant($tenant, TenantRole::Owner)->create();
    $otherTenantCustomer = Customer::factory()->create();

    actAsMemberIn($owner, $tenant);

    expect(customerAbilities($owner, $otherTenantCustomer))->toMatchArray(['view' => false, 'update' => false, 'delete' => false]);
});

<?php

use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\MemberRepositoryInterface;
use App\Tenancy\MembershipEvent;
use App\Tenancy\TenantRole;

it('records who added a member to the tenant history', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
    $newMember = User::factory()->create();
    $this->actingAs($admin);

    app(MemberRepositoryInterface::class)->activate($tenant, $newMember);

    $audit = auditsOf($tenant, MembershipEvent::Activated->value)->first();

    expect($audit->user_id)->toBe($admin->id)
        ->and($audit->tenant_id)->toBe($tenant->id)
        ->and($audit->old_values)->toBe(['user_id' => $newMember->id, 'is_active' => null])
        ->and($audit->new_values)->toBe(['user_id' => $newMember->id, 'is_active' => true]);
});

it('records the removal of a member', function () {
    $tenant = Tenant::factory()->create();
    $member = User::factory()->forTenant($tenant)->create();

    app(MemberRepositoryInterface::class)->deactivate($tenant, $member);

    $audit = auditsOf($tenant, MembershipEvent::Deactivated->value)->sole();

    expect($audit->old_values)->toBe(['user_id' => $member->id, 'is_active' => true])
        ->and($audit->new_values)->toBe(['user_id' => $member->id, 'is_active' => false]);
});

it('records the previous and the new role', function () {
    $tenant = Tenant::factory()->create();
    $member = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();

    app(MemberRepositoryInterface::class)->assignRole($tenant, $member, TenantRole::Manager);

    $audit = auditsOf($tenant, MembershipEvent::RoleChanged->value)->first();

    expect($audit->old_values)->toBe(['user_id' => $member->id, 'role' => 'vistoriador'])
        ->and($audit->new_values)->toBe(['user_id' => $member->id, 'role' => 'gestor']);
});

it('records nothing when a call changes nothing', function () {
    $tenant = Tenant::factory()->create();
    $member = User::factory()->forTenant($tenant, TenantRole::Manager)->create();
    $formerMember = User::factory()->hasAttached($tenant, ['is_active' => false], 'tenants')->create();
    $auditCountBefore = auditsOf($tenant)->count();
    $members = app(MemberRepositoryInterface::class);

    $members->activate($tenant, $member);
    $members->assignRole($tenant, $member, TenantRole::Manager);
    $members->deactivate($tenant, $formerMember);

    expect(auditsOf($tenant)->count())->toBe($auditCountBefore);
});

it('keeps the history of each tenant apart', function () {
    $tenant = Tenant::factory()->create();
    $otherTenant = Tenant::factory()->create();
    $member = User::factory()->create();
    $members = app(MemberRepositoryInterface::class);

    $members->assignRole($tenant, $member, TenantRole::Inspector);
    $members->assignRole($otherTenant, $member, TenantRole::Admin);

    expect(auditsOf($tenant, MembershipEvent::RoleChanged->value)->pluck('new_values.role')->all())->toBe(['vistoriador'])
        ->and(auditsOf($otherTenant, MembershipEvent::RoleChanged->value)->pluck('new_values.role')->all())->toBe(['administrador']);
});

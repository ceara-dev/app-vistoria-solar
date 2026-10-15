<?php

use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\MemberRepositoryInterface;
use App\Tenancy\TenantRole;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    it('lists the members of the current tenant with their roles', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create(['name' => 'Ana']);
        User::factory()->forTenant($tenant, TenantRole::Inspector)->create(['name' => 'Bruno']);
        User::factory()->forTenant(Tenant::factory()->create(), TenantRole::Admin)->create(['name' => 'De outra empresa']);

        $this->actingAs($admin)
            ->get(route('tenant.members.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Members/Index')
                ->has('members.data', 2)
                ->where('members.data.0.name', 'Ana')
                ->where('members.data.0.role', 'administrador')
                ->where('members.data.0.is_current_user', true)
                ->where('members.data.1.name', 'Bruno')
                ->where('members.data.1.role', 'vistoriador'));
    });

    it('forbids members without permission to view members', function () {
        $tenant = Tenant::factory()->create();
        $inspector = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();

        $this->actingAs($inspector)
            ->get(route('tenant.members.index'))
            ->assertForbidden();
    });
});

describe('update', function () {
    it('changes the role of a member', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $member = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();

        $this->actingAs($admin)
            ->put(route('tenant.members.update', $member), ['role' => TenantRole::Manager->value])
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        expect(tenantRoleOf($tenant, $member))->toBe(TenantRole::Manager);
    });

    it('keeps the role of the member in other tenants', function () {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $member = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();
        $members = app(MemberRepositoryInterface::class);
        $members->activate($otherTenant, $member);
        $members->assignRole($otherTenant, $member, TenantRole::Admin);

        $this->actingAs($admin)
            ->put(route('tenant.members.update', $member), ['role' => TenantRole::Manager->value]);

        expect(tenantRoleOf($otherTenant, $member))->toBe(TenantRole::Admin);
    });

    it('rejects changing your own role', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();

        $this->actingAs($admin)
            ->put(route('tenant.members.update', $admin), ['role' => TenantRole::Inspector->value])
            ->assertSessionHasErrors(['member' => 'Você não pode alterar o seu próprio acesso.']);
    });

    it('rejects an administrator promoting someone to owner', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $member = User::factory()->forTenant($tenant, TenantRole::Manager)->create();

        $this->actingAs($admin)
            ->put(route('tenant.members.update', $member), ['role' => TenantRole::Owner->value])
            ->assertSessionHasErrors(['member' => 'Só um proprietário pode alterar proprietários.']);

        expect(tenantRoleOf($tenant, $member))->toBe(TenantRole::Manager);
    });

    it('lets an owner promote someone to owner', function () {
        $tenant = Tenant::factory()->create();
        $owner = User::factory()->forTenant($tenant, TenantRole::Owner)->create();
        $member = User::factory()->forTenant($tenant, TenantRole::Admin)->create();

        $this->actingAs($owner)
            ->put(route('tenant.members.update', $member), ['role' => TenantRole::Owner->value])
            ->assertSessionHasNoErrors();

        expect(tenantRoleOf($tenant, $member))->toBe(TenantRole::Owner);
    });

    it('rejects an unknown role', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $member = User::factory()->forTenant($tenant)->create();

        $this->actingAs($admin)
            ->put(route('tenant.members.update', $member), ['role' => 'superusuario'])
            ->assertSessionHasErrors(['role' => 'O valor selecionado é inválido']);
    });

    it('forbids members without permission to manage members', function () {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant, TenantRole::Manager)->create();
        $member = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();

        $this->actingAs($manager)
            ->put(route('tenant.members.update', $member), ['role' => TenantRole::Manager->value])
            ->assertForbidden();
    });

    it('returns 404 for a user of another tenant', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $outsider = User::factory()->forTenant(Tenant::factory()->create(), TenantRole::Inspector)->create();

        $this->actingAs($admin)
            ->put(route('tenant.members.update', $outsider), ['role' => TenantRole::Manager->value])
            ->assertNotFound();
    });
});

describe('destroy', function () {
    it('removes the access of a member and clears it as their current tenant', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $member = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();

        $this->actingAs($admin)
            ->delete(route('tenant.members.destroy', $member))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        $member->refresh();

        expect($member->isActiveMemberOf($tenant))->toBeFalse()
            ->and($member->current_tenant_id)->toBeNull();
    });

    it('rejects removing your own access', function () {
        $tenant = Tenant::factory()->create();
        $owner = User::factory()->forTenant($tenant, TenantRole::Owner)->create();

        $this->actingAs($owner)
            ->delete(route('tenant.members.destroy', $owner))
            ->assertSessionHasErrors('member');

        expect($owner->isActiveMemberOf($tenant))->toBeTrue();
    });

    it('rejects an administrator removing an owner', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $owner = User::factory()->forTenant($tenant, TenantRole::Owner)->create();

        $this->actingAs($admin)
            ->delete(route('tenant.members.destroy', $owner))
            ->assertSessionHasErrors(['member' => 'Só um proprietário pode alterar proprietários.']);

        expect($owner->isActiveMemberOf($tenant))->toBeTrue();
    });

    it('forbids members without permission to manage members', function () {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant, TenantRole::Manager)->create();
        $member = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();

        $this->actingAs($manager)
            ->delete(route('tenant.members.destroy', $member))
            ->assertForbidden();
    });

    it('returns 404 for a user of another tenant', function () {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->forTenant($tenant, TenantRole::Admin)->create();
        $outsider = User::factory()->forTenant(Tenant::factory()->create())->create();

        $this->actingAs($admin)
            ->delete(route('tenant.members.destroy', $outsider))
            ->assertNotFound();

        expect($outsider->current_tenant_id)->not->toBeNull();
    });
});

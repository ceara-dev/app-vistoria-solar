<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\MemberRepositoryInterface;
use App\Tenancy\MembershipEvent;
use App\Tenancy\TenantAuditTrail;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantRole;
use Illuminate\Database\Eloquent\Collection;

/**
 * Vínculos entre usuários e empresas (`tenant_user`) e o papel de cada um.
 *
 * O spatie filtra os papéis pela empresa do contexto, então toda leitura ou
 * gravação de papel roda dentro da empresa informada. Toda mudança efetiva
 * vai para o histórico da empresa; chamadas que não mudam nada não gravam.
 */
class MemberRepository implements MemberRepositoryInterface
{
    public function __construct(
        private TenantContext $tenantContext,
        private TenantAuditTrail $auditTrail,
    ) {}

    /**
     * @return Collection<int, User>
     */
    public function membersOf(Tenant $tenant): Collection
    {
        return $this->tenantContext->run($tenant, fn (): Collection => $tenant->users()
            ->with('roles')
            ->orderByPivot('is_active', 'desc')
            ->orderBy('name')
            ->get());
    }

    public function isActiveMember(Tenant $tenant, User $user): bool
    {
        return $user->isActiveMemberOf($tenant);
    }

    public function hasActiveMemberWithEmail(Tenant $tenant, string $email): bool
    {
        return $tenant->users()->wherePivot('is_active', true)->where('email', $email)->exists();
    }

    public function activate(Tenant $tenant, User $user): void
    {
        $wasActive = $this->membershipStatus($tenant, $user);

        if ($wasActive === true) {
            return;
        }

        $tenant->users()->syncWithoutDetaching([$user->id => ['is_active' => true]]);

        $this->auditTrail->record(
            $tenant,
            MembershipEvent::Activated,
            ['user_id' => $user->id, 'is_active' => $wasActive],
            ['user_id' => $user->id, 'is_active' => true],
        );
    }

    public function deactivate(Tenant $tenant, User $user): void
    {
        if ($this->membershipStatus($tenant, $user) !== true) {
            return;
        }

        $tenant->users()->updateExistingPivot($user->id, ['is_active' => false]);

        $this->auditTrail->record(
            $tenant,
            MembershipEvent::Deactivated,
            ['user_id' => $user->id, 'is_active' => true],
            ['user_id' => $user->id, 'is_active' => false],
        );
    }

    public function roleOf(Tenant $tenant, User $user): ?TenantRole
    {
        $roleName = $this->tenantContext->run($tenant, fn (): ?string => $user->roles()->value('name'));

        return $roleName === null ? null : TenantRole::tryFrom($roleName);
    }

    public function assignRole(Tenant $tenant, User $user, TenantRole $role): void
    {
        $previousRole = $this->roleOf($tenant, $user);

        if ($previousRole === $role) {
            return;
        }

        $this->tenantContext->run($tenant, function () use ($user, $role): void {
            $user->unsetRelation('roles')->syncRoles([$role->value]);
        });

        $this->auditTrail->record(
            $tenant,
            MembershipEvent::RoleChanged,
            ['user_id' => $user->id, 'role' => $previousRole?->value],
            ['user_id' => $user->id, 'role' => $role->value],
        );
    }

    /**
     * @return list<string>
     */
    public function permissionsOf(Tenant $tenant, User $user): array
    {
        return $this->tenantContext->run($tenant, fn (): array => $user
            ->unsetRelation('roles')
            ->unsetRelation('permissions')
            ->getAllPermissions()
            ->pluck('name')
            ->values()
            ->all());
    }

    /**
     * `true` ativo, `false` desativado, `null` sem vínculo.
     */
    private function membershipStatus(Tenant $tenant, User $user): ?bool
    {
        $isActive = $tenant->users()->newPivotStatementForId($user->id)->value('is_active');

        return $isActive === null ? null : (bool) $isActive;
    }
}

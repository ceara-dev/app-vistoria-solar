<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Repositories\Contracts\MemberRepositoryInterface;
use App\Repositories\Contracts\TenantInvitationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Tenancy\TenantRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Gestão dos membros de uma empresa.
 */
class MemberService
{
    public function __construct(
        protected MemberRepositoryInterface $members,
        protected UserRepositoryInterface $users,
        protected TenantInvitationRepositoryInterface $invitations,
    ) {}

    /**
     * @return Collection<int, User>
     */
    public function list(Tenant $tenant): Collection
    {
        return $this->members->membersOf($tenant);
    }

    /**
     * Convites pendentes da empresa atual, exibidos junto com os membros.
     *
     * @return Collection<int, TenantInvitation>
     */
    public function pendingInvitations(): Collection
    {
        return $this->invitations->pending();
    }

    /**
     * @throws ValidationException
     */
    public function changeRole(Tenant $tenant, User $actor, User $member, TenantRole $role): void
    {
        $this->ensureActorCanManage($tenant, $actor, $member, $role);

        $this->members->assignRole($tenant, $member, $role);
    }

    /**
     * Remove o acesso do membro à empresa, mantendo o histórico do vínculo.
     *
     * @throws ValidationException
     */
    public function deactivate(Tenant $tenant, User $actor, User $member): void
    {
        $this->ensureActorCanManage($tenant, $actor, $member);

        DB::transaction(function () use ($tenant, $member): void {
            $this->members->deactivate($tenant, $member);

            if ($member->current_tenant_id === $tenant->id) {
                $this->users->setCurrentTenant($member, null);
            }
        });
    }

    /**
     * Ninguém altera o próprio acesso, e só proprietários mexem em proprietários.
     * Assim a empresa nunca fica sem proprietário.
     *
     * @throws ValidationException
     */
    private function ensureActorCanManage(Tenant $tenant, User $actor, User $member, ?TenantRole $newRole = null): void
    {
        if ($actor->is($member)) {
            throw ValidationException::withMessages(['member' => 'Você não pode alterar o seu próprio acesso.']);
        }

        $affectsOwner = $newRole === TenantRole::Owner
            || $this->members->roleOf($tenant, $member) === TenantRole::Owner;

        if ($affectsOwner && $this->members->roleOf($tenant, $actor) !== TenantRole::Owner) {
            throw ValidationException::withMessages(['member' => 'Só um proprietário pode alterar proprietários.']);
        }
    }
}

<?php

namespace App\Repositories\Contracts;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantRole;
use Illuminate\Database\Eloquent\Collection;

interface MemberRepositoryInterface
{
    /**
     * Membros da empresa, ativos e inativos, com o papel de cada um nela.
     *
     * @return Collection<int, User>
     */
    public function membersOf(Tenant $tenant): Collection;

    public function isActiveMember(Tenant $tenant, User $user): bool;

    public function hasActiveMemberWithEmail(Tenant $tenant, string $email): bool;

    public function activate(Tenant $tenant, User $user): void;

    public function deactivate(Tenant $tenant, User $user): void;

    public function roleOf(Tenant $tenant, User $user): ?TenantRole;

    public function assignRole(Tenant $tenant, User $user, TenantRole $role): void;

    /**
     * Nomes das permissões do usuário na empresa.
     *
     * @return list<string>
     */
    public function permissionsOf(Tenant $tenant, User $user): array;
}

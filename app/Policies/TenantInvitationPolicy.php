<?php

namespace App\Policies;

use App\Models\TenantInvitation;
use App\Models\User;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantPermission;

/**
 * Molde das policies de módulos de empresa: a permissão do papel decide, e as
 * ações sobre um registro conferem também se ele é da empresa atual.
 */
class TenantInvitationPolicy
{
    public function __construct(private TenantContext $tenantContext) {}

    public function create(User $user): bool
    {
        return $user->can(TenantPermission::MembersManage->value);
    }

    public function delete(User $user, TenantInvitation $invitation): bool
    {
        return $this->tenantContext->owns($invitation)
            && $user->can(TenantPermission::MembersManage->value);
    }
}

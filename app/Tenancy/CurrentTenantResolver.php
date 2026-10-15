<?php

namespace App\Tenancy;

use App\Models\Tenant;
use App\Models\User;

/**
 * Descobre em qual empresa o usuário está trabalhando.
 */
class CurrentTenantResolver
{
    /**
     * Mantém `current_tenant_id` enquanto o vínculo estiver ativo. Sem ele,
     * adota a única empresa ativa do usuário ou, se houver mais de uma,
     * limpa a empresa atual para que ele escolha.
     */
    public function resolve(User $user): ?Tenant
    {
        // Consulta direta: a relação já carregada pode ser de antes de uma troca de empresa.
        $currentTenant = $user->currentTenant()->first();

        if ($currentTenant !== null && $user->isActiveMemberOf($currentTenant)) {
            $user->setRelation('currentTenant', $currentTenant);

            return $currentTenant;
        }

        $activeTenants = $user->activeTenants()->limit(2)->get();
        $resolvedTenant = $activeTenants->count() === 1 ? $activeTenants->first() : null;

        $user->forceFill(['current_tenant_id' => $resolvedTenant?->id])->save();
        $user->setRelation('currentTenant', $resolvedTenant);

        return $resolvedTenant;
    }
}

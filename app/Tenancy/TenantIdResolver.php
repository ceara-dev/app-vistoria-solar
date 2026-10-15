<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

/**
 * Empresa de cada auditoria (coluna `audits.tenant_id`).
 *
 * Vale primeiro a empresa do próprio registro auditado, porque ele pode ser
 * alterado sem empresa no contexto (aceite de convite, Central, console).
 */
class TenantIdResolver implements Resolver
{
    public static function resolve(Auditable $auditable): ?int
    {
        if ($auditable instanceof Tenant) {
            return $auditable->getKey();
        }

        $tenantId = $auditable instanceof Model ? $auditable->getAttribute('tenant_id') : null;

        return $tenantId === null ? app(TenantContext::class)->id() : (int) $tenantId;
    }
}

<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\Event;
use OwenIt\Auditing\Events\AuditCustom;

/**
 * Grava no histórico da empresa as mudanças que não são de um model próprio,
 * como vínculos (`tenant_user`) e papéis (tabelas do spatie).
 *
 * O laravel-auditing tem `auditSync()` para pivots, mas ele compara só as
 * chaves: mudar `is_active` de um membro gerava uma auditoria vazia.
 */
class TenantAuditTrail
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function record(Tenant $tenant, MembershipEvent $event, array $oldValues, array $newValues): void
    {
        $tenant->auditEvent = $event->value;
        $tenant->isCustomEvent = true;
        $tenant->auditCustomOld = $oldValues;
        $tenant->auditCustomNew = $newValues;

        try {
            Event::dispatch(new AuditCustom($tenant));
        } finally {
            $tenant->isCustomEvent = false;
            $tenant->auditCustomOld = $tenant->auditCustomNew = [];
        }
    }
}

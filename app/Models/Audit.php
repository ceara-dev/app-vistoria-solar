<?php

namespace App\Models;

use App\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use OwenIt\Auditing\Models\Audit as BaseAudit;

/**
 * Histórico de alterações, restrito à empresa atual.
 *
 * Usa só o escopo, sem o BelongsToTenant: o pacote cria as auditorias com o
 * `tenant_id` do TenantIdResolver, inclusive nulo para ações da Central.
 */
#[ScopedBy(TenantScope::class)]
class Audit extends BaseAudit
{
    //
}

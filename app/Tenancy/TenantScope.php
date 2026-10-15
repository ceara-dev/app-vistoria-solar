<?php

namespace App\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restringe as consultas à empresa do contexto atual.
 *
 * Sem empresa definida, a consulta lança exceção em vez de trazer as linhas
 * de todas as empresas.
 */
class TenantScope implements Scope
{
    /**
     * @throws TenantNotResolvedException
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(
            $model->qualifyColumn('tenant_id'),
            app(TenantContext::class)->require()->id,
        );
    }
}

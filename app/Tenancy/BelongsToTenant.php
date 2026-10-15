<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Para models cujas linhas pertencem a uma empresa.
 *
 * Aplica o TenantScope, preenche `tenant_id` na criação e impede que a
 * empresa de um registro seja trocada.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            $currentTenantId = app(TenantContext::class)->id();

            if ($model->tenant_id === null) {
                $model->tenant_id = $currentTenantId ?? throw new TenantNotResolvedException;

                return;
            }

            if ($currentTenantId !== null && (int) $model->tenant_id !== $currentTenantId) {
                throw new LogicException('Registro de outra empresa criado dentro do contexto atual.');
            }
        });

        static::updating(function (Model $model): void {
            if ($model->isDirty('tenant_id')) {
                throw new LogicException('A empresa de um registro não pode ser alterada.');
            }
        });
    }

    /**
     * `tenant_id` sempre como inteiro, para comparar com o TenantContext.
     */
    public function initializeBelongsToTenant(): void
    {
        $this->mergeCasts(['tenant_id' => 'integer']);
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

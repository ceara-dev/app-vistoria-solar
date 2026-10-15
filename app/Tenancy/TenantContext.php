<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;

/**
 * Empresa da requisição ou do job em execução.
 *
 * Registrado como `scoped` no container: é descartado ao fim de cada
 * requisição e antes de cada job no worker.
 */
class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;

        setPermissionsTeamId($tenant?->id);

        if ($tenant === null) {
            Context::forget('tenant_id');
        } else {
            Context::add('tenant_id', $tenant->id);
        }
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    /**
     * Indica se o registro é da empresa atual. Sem empresa no contexto, nada é.
     */
    public function owns(Model $model): bool
    {
        return $this->tenant !== null && (int) $model->getAttribute('tenant_id') === $this->tenant->id;
    }

    /**
     * @throws TenantNotResolvedException
     */
    public function require(): Tenant
    {
        return $this->tenant ?? throw new TenantNotResolvedException;
    }

    /**
     * Executa o callback dentro da empresa informada e restaura a anterior.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function run(Tenant $tenant, callable $callback): mixed
    {
        $previous = $this->tenant;
        $this->set($tenant);

        try {
            return $callback();
        } finally {
            $this->set($previous);
        }
    }
}

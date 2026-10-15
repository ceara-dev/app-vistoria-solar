<?php

namespace App\Tenancy;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

/**
 * Regras `exists` e `unique` restritas à empresa atual.
 *
 * As regras de validação consultam o banco sem passar pelo TenantScope, então
 * toda tabela de empresa deve ser validada por aqui.
 */
final class TenantRule
{
    /**
     * @throws TenantNotResolvedException
     */
    public static function exists(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where('tenant_id', self::currentTenantId());
    }

    /**
     * @throws TenantNotResolvedException
     */
    public static function unique(string $table, string $column): Unique
    {
        return Rule::unique($table, $column)->where('tenant_id', self::currentTenantId());
    }

    /**
     * @throws TenantNotResolvedException
     */
    private static function currentTenantId(): int
    {
        return app(TenantContext::class)->require()->id;
    }
}

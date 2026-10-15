<?php

namespace App\Models;

use Database\Factories\B2bClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cliente B2B da plataforma: a empresa que contrata o SaaS. Global, mantido
 * pela Central. Empresas (`Tenant`) apontam para o cliente.
 */
#[Fillable(['legal_name', 'trade_name', 'document', 'contact_name', 'contact_email', 'contact_phone'])]
class B2bClient extends Model
{
    /** @use HasFactory<B2bClientFactory> */
    use HasFactory;

    /**
     * Nome de exibição: fantasia quando existe, senão a razão social.
     */
    public function displayName(): string
    {
        return $this->trade_name ?? $this->legal_name;
    }

    /**
     * @return HasMany<Tenant, $this>
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }
}

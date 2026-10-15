<?php

namespace App\Models;

use App\Tenancy\TenantStatus;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

#[Fillable(['name', 'legal_name', 'document', 'plan_id', 'b2b_client_id', 'settings'])]
class Tenant extends Model implements AuditableContract
{
    /** @use HasFactory<TenantFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * Espelha o padrão da migration para empresas ainda não salvas.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'trial',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'trial_ends_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('is_active')->withTimestamps();
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return BelongsTo<B2bClient, $this>
     */
    public function b2bClient(): BelongsTo
    {
        return $this->belongsTo(B2bClient::class);
    }

    /**
     * Indica se os membros podem usar o sistema nesta empresa.
     */
    public function allowsAccess(): bool
    {
        return match ($this->status) {
            TenantStatus::Active => true,
            TenantStatus::Trial => $this->trial_ends_at === null || $this->trial_ends_at->isFuture(),
            TenantStatus::Suspended, TenantStatus::Canceled => false,
        };
    }

    /**
     * Explica por que os membros não podem usar o sistema, ou `null` se podem.
     */
    public function blockedReason(): ?string
    {
        if ($this->allowsAccess()) {
            return null;
        }

        return match ($this->status) {
            TenantStatus::Trial => 'O período de teste desta empresa terminou.',
            TenantStatus::Suspended => 'O acesso desta empresa está suspenso.',
            TenantStatus::Canceled => 'Esta empresa foi cancelada.',
            TenantStatus::Active => null,
        };
    }
}

<?php

namespace App\Models;

use App\Tenancy\BelongsToTenant;
use Database\Factories\TripFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Rota gravada pelo app de campo. O id vem do aparelho.
 */
#[Fillable(['started_at', 'finished_at', 'distance_m', 'moving_seconds', 'paused_seconds', 'max_speed_ms'])]
class Trip extends Model implements AuditableContract
{
    /** @use HasFactory<TripFactory> */
    use Auditable, BelongsToTenant, HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'distance_m' => 'float',
            'moving_seconds' => 'integer',
            'paused_seconds' => 'integer',
            'max_speed_ms' => 'float',
            'user_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<TripPoint, $this>
     */
    public function points(): HasMany
    {
        return $this->hasMany(TripPoint::class);
    }
}

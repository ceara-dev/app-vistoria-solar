<?php

namespace App\Models;

use App\Enums\TripPointStatus;
use App\Tenancy\BelongsToTenant;
use Database\Factories\TripPointFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ponto de GPS de uma rota. Chega em lote pela API (TripRepository::addPoints).
 */
#[Fillable(['latitude', 'longitude', 'recorded_at', 'status', 'speed_ms'])]
#[WithoutTimestamps]
class TripPoint extends Model
{
    /** @use HasFactory<TripPointFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'recorded_at' => 'datetime',
            'status' => TripPointStatus::class,
            'speed_ms' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Trip, $this>
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}

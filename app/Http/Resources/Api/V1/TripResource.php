<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Trip
 */
class TripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'started_at' => $this->started_at->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
            'distance_m' => $this->distance_m,
            'moving_seconds' => $this->moving_seconds,
            'paused_seconds' => $this->paused_seconds,
            'max_speed_ms' => $this->max_speed_ms,
            'points_count' => $this->whenCounted('points'),
        ];
    }
}

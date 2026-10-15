<?php

namespace App\Http\Resources\Central;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Plan
 */
class PlanResource extends JsonResource
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
            'name' => $this->name,
            'price_cents' => $this->price_cents,
            'limits' => $this->limits,
            'is_active' => $this->is_active,
            'tenants_count' => $this->whenCounted('tenants_count'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

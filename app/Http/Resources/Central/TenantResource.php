<?php

namespace App\Http\Resources\Central;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Os contadores (`members_count`, `customers_count`) vêm do repositório da Central.
 *
 * @mixin Tenant
 */
class TenantResource extends JsonResource
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
            'document' => $this->document,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'plan' => $this->plan === null ? null : [
                'id' => $this->plan->id,
                'name' => $this->plan->name,
            ],
            'b2b_client' => $this->b2bClient === null ? null : [
                'id' => $this->b2bClient->id,
                'name' => $this->b2bClient->displayName(),
            ],
            'members_count' => (int) $this->members_count,
            'customers_count' => (int) $this->customers_count,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

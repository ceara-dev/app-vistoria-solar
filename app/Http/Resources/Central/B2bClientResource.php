<?php

namespace App\Http\Resources\Central;

use App\Models\B2bClient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin B2bClient
 */
class B2bClientResource extends JsonResource
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
            'legal_name' => $this->legal_name,
            'trade_name' => $this->trade_name,
            'document' => $this->document,
            'contact_name' => $this->contact_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'tenants_count' => $this->whenCounted('tenants_count'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

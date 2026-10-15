<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\TenantSummaryResource;
use App\Services\ApiSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ApiSession $resource
 */
class ApiSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $session = $this->resource;

        return [
            $this->mergeWhen($session->plainTextToken !== null, fn (): array => [
                'token' => $session->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $session->expiresAt?->toIso8601String(),
            ]),
            'user' => $session->user->only('id', 'name', 'email'),
            'tenant' => (new TenantSummaryResource($session->tenant))->resolve($request),
            'permissions' => $session->permissions,
        ];
    }
}

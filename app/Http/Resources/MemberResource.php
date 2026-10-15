<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Tenancy\TenantRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Membro da empresa atual. Espera os papéis já carregados na empresa.
 *
 * @mixin User
 */
class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $role = TenantRole::tryFrom((string) $this->roles->first()?->name);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $role?->value,
            'role_label' => $role?->label(),
            'is_active' => (bool) $this->pivot->is_active,
            'is_current_user' => $request->user()?->is($this->resource) ?? false,
        ];
    }
}

<?php

namespace App\Repositories;

use App\Models\TenantInvitation;
use App\Models\User;
use App\Repositories\Contracts\TenantInvitationRepositoryInterface;
use App\Tenancy\TenantRole;
use App\Tenancy\TenantScope;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class TenantInvitationRepository implements TenantInvitationRepositoryInterface
{
    /**
     * @return Collection<int, TenantInvitation>
     */
    public function pending(): Collection
    {
        return TenantInvitation::pending()->orderBy('email')->get();
    }

    public function createOrRenew(string $email, TenantRole $role, string $tokenHash, CarbonInterface $expiresAt, User $inviter): TenantInvitation
    {
        $invitation = TenantInvitation::firstOrNew(['email' => $email]);
        $invitation->fill(['role' => $role]);
        $invitation->forceFill([
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'accepted_at' => null,
            'invited_by' => $inviter->id,
        ])->save();

        return $invitation;
    }

    /**
     * Quem abre o link ainda não está em nenhuma empresa, então a busca
     * atravessa as empresas. O hash do token identifica um único convite.
     */
    public function findPendingByTokenHash(string $tokenHash): ?TenantInvitation
    {
        return TenantInvitation::withoutGlobalScope(TenantScope::class)
            ->with('tenant')
            ->pending()
            ->where('token_hash', $tokenHash)
            ->first();
    }

    public function markAccepted(TenantInvitation $invitation): void
    {
        $invitation->forceFill(['accepted_at' => now()])->save();
    }

    public function delete(TenantInvitation $invitation): void
    {
        $invitation->delete();
    }
}

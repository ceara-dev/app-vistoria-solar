<?php

namespace App\Repositories\Contracts;

use App\Models\TenantInvitation;
use App\Models\User;
use App\Tenancy\TenantRole;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

interface TenantInvitationRepositoryInterface
{
    /**
     * Convites pendentes da empresa atual.
     *
     * @return Collection<int, TenantInvitation>
     */
    public function pending(): Collection;

    /**
     * Cria o convite do e-mail na empresa atual ou renova o que já existe.
     */
    public function createOrRenew(string $email, TenantRole $role, string $tokenHash, CarbonInterface $expiresAt, User $inviter): TenantInvitation;

    public function findPendingByTokenHash(string $tokenHash): ?TenantInvitation;

    public function markAccepted(TenantInvitation $invitation): void;

    public function delete(TenantInvitation $invitation): void;
}

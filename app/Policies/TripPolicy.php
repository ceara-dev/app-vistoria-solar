<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantPermission;

/**
 * Cada pessoa envia e altera só as próprias rotas.
 */
class TripPolicy
{
    public function __construct(private TenantContext $tenantContext) {}

    public function viewAny(User $user): bool
    {
        return $user->can(TenantPermission::TripsRecord->value);
    }

    public function create(User $user): bool
    {
        return $user->can(TenantPermission::TripsRecord->value);
    }

    public function update(User $user, Trip $trip): bool
    {
        return $this->tenantContext->owns($trip)
            && $trip->user_id === $user->id
            && $user->can(TenantPermission::TripsRecord->value);
    }
}

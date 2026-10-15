<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Tenancy\TenantStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class TenantRepository implements TenantRepositoryInterface
{
    public function createTrial(string $name, CarbonInterface $trialEndsAt): Tenant
    {
        $tenant = new Tenant(['name' => $name]);
        $tenant->forceFill(['status' => TenantStatus::Trial, 'trial_ends_at' => $trialEndsAt])->save();

        return $tenant;
    }

    /**
     * @return Collection<int, Tenant>
     */
    public function activeTenantsOf(User $user): Collection
    {
        return $user->activeTenants()->orderBy('name')->get();
    }

    public function findActiveTenantOf(User $user, int $tenantId): ?Tenant
    {
        return $user->activeTenants()->whereKey($tenantId)->first();
    }
}

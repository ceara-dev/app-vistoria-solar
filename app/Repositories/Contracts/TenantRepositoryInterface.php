<?php

namespace App\Repositories\Contracts;

use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

interface TenantRepositoryInterface
{
    public function createTrial(string $name, CarbonInterface $trialEndsAt): Tenant;

    /**
     * @return Collection<int, Tenant>
     */
    public function activeTenantsOf(User $user): Collection;

    public function findActiveTenantOf(User $user, int $tenantId): ?Tenant;
}

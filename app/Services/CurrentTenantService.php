<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Escolha e troca da empresa em que o usuário está trabalhando.
 */
class CurrentTenantService
{
    public function __construct(
        protected TenantRepositoryInterface $tenants,
        protected UserRepositoryInterface $users,
        protected RegistrationService $registration,
    ) {}

    /**
     * @return Collection<int, Tenant>
     */
    public function availableTenants(User $user): Collection
    {
        return $this->tenants->activeTenantsOf($user);
    }

    /**
     * @throws ValidationException
     */
    public function switchTo(User $user, int $tenantId): Tenant
    {
        $tenant = $this->tenants->findActiveTenantOf($user, $tenantId)
            ?? throw ValidationException::withMessages(['tenant_id' => __('validation.exists')]);

        $this->users->setCurrentTenant($user, $tenant);

        return $tenant;
    }

    public function createTenant(User $user, string $companyName): Tenant
    {
        return $this->registration->createTenantFor($user, $companyName);
    }

    /**
     * Empresa atual do usuário, mesmo que ela esteja bloqueada.
     */
    public function currentTenantOf(User $user): ?Tenant
    {
        return $user->current_tenant_id === null
            ? null
            : $this->tenants->findActiveTenantOf($user, $user->current_tenant_id);
    }
}

<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\MemberRepositoryInterface;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Tenancy\TenantRole;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Cadastro público: cria a conta e a empresa da pessoa, que vira proprietária.
 */
class RegistrationService
{
    public const TRIAL_DAYS = 14;

    public function __construct(
        protected UserRepositoryInterface $users,
        protected TenantRepositoryInterface $tenants,
        protected MemberRepositoryInterface $members,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string, company_name: string}  $data
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = $this->users->create(Arr::only($data, ['name', 'email', 'password']));
            $this->createTenantFor($user, $data['company_name']);

            return $user;
        });
    }

    /**
     * Cria uma empresa em período de teste com o usuário como proprietário e
     * a define como a empresa atual dele.
     */
    public function createTenantFor(User $owner, string $companyName): Tenant
    {
        return DB::transaction(function () use ($owner, $companyName): Tenant {
            $tenant = $this->tenants->createTrial($companyName, now()->addDays(self::TRIAL_DAYS));

            $this->members->activate($tenant, $owner);
            $this->members->assignRole($tenant, $owner, TenantRole::Owner);
            $this->users->setCurrentTenant($owner, $tenant);

            return $tenant;
        });
    }
}

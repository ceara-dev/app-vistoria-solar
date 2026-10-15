<?php

namespace App\Repositories\Central\Contracts;

use App\Models\Tenant;
use App\Tenancy\TenantStatus;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Consultas da Central: atravessam empresas de propósito.
 */
interface TenantRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Tenant>
     */
    public function paginate(?string $search, ?TenantStatus $status, int $perPage): LengthAwarePaginator;

    public function createTrial(string $name, CarbonInterface $trialEndsAt, ?int $planId, ?int $b2bClientId): Tenant;

    public function updateStatus(Tenant $tenant, TenantStatus $status): void;

    public function extendTrial(Tenant $tenant, CarbonInterface $trialEndsAt): void;

    /**
     * Total de empresas por status, com a chave do enum.
     *
     * @return array<string, int>
     */
    public function statusCounts(): array;

    /**
     * Clientes por empresa, para a lista da Central.
     *
     * @param  list<int>  $tenantIds
     * @return array<int, int>
     */
    public function customersCountByTenant(array $tenantIds): array;
}

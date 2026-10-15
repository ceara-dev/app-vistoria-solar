<?php

namespace App\Services\Central;

use App\Repositories\Central\Contracts\PlanRepositoryInterface;
use App\Repositories\Central\Contracts\TenantRepositoryInterface;
use App\Tenancy\TenantStatus;

/**
 * Indicadores do painel da Central.
 */
class DashboardService
{
    public function __construct(
        protected TenantRepositoryInterface $tenants,
        protected PlanRepositoryInterface $plans,
    ) {}

    /**
     * @return array{tenants: array<string, int>, plans: int}
     */
    public function stats(): array
    {
        $counts = $this->tenants->statusCounts();

        $tenants = ['total' => 0];

        foreach (TenantStatus::cases() as $status) {
            $count = $counts[$status->value] ?? 0;
            $tenants[$status->value] = $count;
            $tenants['total'] += $count;
        }

        return [
            'tenants' => $tenants,
            'plans' => $this->plans->count(),
        ];
    }
}

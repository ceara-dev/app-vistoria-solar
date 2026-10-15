<?php

namespace App\Repositories\Central;

use App\Models\Customer;
use App\Models\Tenant;
use App\Repositories\Central\Contracts\TenantRepositoryInterface;
use App\Tenancy\TenantScope;
use App\Tenancy\TenantStatus;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * A Central enxerga todas as empresas. `tenants` é tabela de plataforma, sem
 * escopo; os dados de empresa (clientes) exigem `withoutGlobalScope` e ficam
 * concentrados aqui, com teste próprio.
 */
class TenantRepository implements TenantRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Tenant>
     */
    public function paginate(?string $search, ?TenantStatus $status, int $perPage): LengthAwarePaginator
    {
        return Tenant::query()
            ->with(['plan:id,name', 'b2bClient:id,legal_name,trade_name'])
            ->withCount(['users as members_count' => fn (Builder $query) => $query->where('tenant_user.is_active', true)])
            ->when($search, function (Builder $query, string $search): void {
                $digits = preg_replace('/\D/', '', $search);

                $query->where(function (Builder $query) use ($search, $digits): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->when($digits !== '', fn (Builder $query) => $query->orWhere('document', 'like', "{$digits}%"));
                });
            })
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createTrial(string $name, CarbonInterface $trialEndsAt, ?int $planId, ?int $b2bClientId): Tenant
    {
        $tenant = new Tenant(['name' => $name]);
        $tenant->forceFill([
            'plan_id' => $planId,
            'b2b_client_id' => $b2bClientId,
            'status' => TenantStatus::Trial,
            'trial_ends_at' => $trialEndsAt,
        ])->save();

        return $tenant;
    }

    public function updateStatus(Tenant $tenant, TenantStatus $status): void
    {
        $tenant->forceFill(['status' => $status])->save();
    }

    public function extendTrial(Tenant $tenant, CarbonInterface $trialEndsAt): void
    {
        $tenant->forceFill(['trial_ends_at' => $trialEndsAt])->save();
    }

    /**
     * @return array<string, int>
     */
    public function statusCounts(): array
    {
        return Tenant::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn (mixed $count): int => (int) $count)
            ->all();
    }

    /**
     * @param  list<int>  $tenantIds
     * @return array<int, int>
     */
    public function customersCountByTenant(array $tenantIds): array
    {
        if ($tenantIds === []) {
            return [];
        }

        return Customer::withoutGlobalScope(TenantScope::class)
            ->whereIn('tenant_id', $tenantIds)
            ->selectRaw('tenant_id, count(*) as aggregate')
            ->groupBy('tenant_id')
            ->pluck('aggregate', 'tenant_id')
            ->map(fn (mixed $count): int => (int) $count)
            ->all();
    }
}

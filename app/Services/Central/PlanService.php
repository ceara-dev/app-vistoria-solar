<?php

namespace App\Services\Central;

use App\Models\Plan;
use App\Repositories\Central\Contracts\PlanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Planos comerciais da plataforma. Os limites são guardados, mas ainda não
 * fiscalizados (decisão de 15/10/2026): entram em vigor com a cobrança.
 */
class PlanService
{
    public function __construct(
        protected PlanRepositoryInterface $plans,
    ) {}

    /**
     * @return Collection<int, Plan>
     */
    public function list(): Collection
    {
        return $this->plans->allWithTenantsCount();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Plan
    {
        return DB::transaction(fn (): Plan => $this->plans->create($this->normalizeLimits($data)));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Plan $plan, array $data): Plan
    {
        return DB::transaction(fn (): Plan => $this->plans->update($plan, $this->normalizeLimits($data)));
    }

    /**
     * Sem nenhum limite informado o plano fica `null`, que a tela lê como
     * "Ilimitado".
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeLimits(array $data): array
    {
        $limits = array_filter($data['limits'] ?? [], fn (mixed $value): bool => $value !== null);

        $data['limits'] = $limits === [] ? null : $limits;

        return $data;
    }
}

<?php

namespace App\Repositories\Central;

use App\Models\Plan;
use App\Repositories\Central\Contracts\PlanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PlanRepository implements PlanRepositoryInterface
{
    /**
     * @return Collection<int, Plan>
     */
    public function allWithTenantsCount(): Collection
    {
        return Plan::query()
            ->withCount('tenants')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Plan>
     */
    public function active(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Plan
    {
        return Plan::create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Plan $plan, array $attributes): Plan
    {
        $plan->update($attributes);

        return $plan;
    }

    public function count(): int
    {
        return Plan::query()->count();
    }
}

<?php

namespace App\Repositories\Central\Contracts;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

interface PlanRepositoryInterface
{
    /**
     * @return Collection<int, Plan>
     */
    public function allWithTenantsCount(): Collection;

    /**
     * Planos que podem ser escolhidos para uma empresa nova.
     *
     * @return Collection<int, Plan>
     */
    public function active(): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Plan;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Plan $plan, array $attributes): Plan;

    public function count(): int;
}

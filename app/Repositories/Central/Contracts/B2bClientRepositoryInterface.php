<?php

namespace App\Repositories\Central\Contracts;

use App\Models\B2bClient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface B2bClientRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, B2bClient>
     */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator;

    /**
     * Todos os clientes, para os seletores da Central.
     *
     * @return Collection<int, B2bClient>
     */
    public function all(): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): B2bClient;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(B2bClient $client, array $attributes): B2bClient;

    public function delete(B2bClient $client): void;
}

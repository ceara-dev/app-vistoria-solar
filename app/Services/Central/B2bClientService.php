<?php

namespace App\Services\Central;

use App\Models\B2bClient;
use App\Repositories\Central\Contracts\B2bClientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Clientes B2B da plataforma, mantidos pela Central.
 */
class B2bClientService
{
    public function __construct(
        protected B2bClientRepositoryInterface $clients,
    ) {}

    /**
     * @return LengthAwarePaginator<int, B2bClient>
     */
    public function paginate(?string $search): LengthAwarePaginator
    {
        return $this->clients->paginate($search, 15);
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public function options(): array
    {
        return $this->clients->all()
            ->map(fn (B2bClient $client): array => ['value' => $client->id, 'label' => $client->displayName()])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): B2bClient
    {
        return DB::transaction(fn (): B2bClient => $this->clients->create($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(B2bClient $client, array $data): B2bClient
    {
        return DB::transaction(fn (): B2bClient => $this->clients->update($client, $data));
    }

    /**
     * As empresas vinculadas ficam sem o cliente (`nullOnDelete`), sem perder dados.
     */
    public function delete(B2bClient $client): void
    {
        DB::transaction(fn () => $this->clients->delete($client));
    }
}

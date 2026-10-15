<?php

namespace App\Repositories\Central;

use App\Models\B2bClient;
use App\Repositories\Central\Contracts\B2bClientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class B2bClientRepository implements B2bClientRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, B2bClient>
     */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return B2bClient::query()
            ->withCount('tenants')
            ->when($search, function (Builder $query, string $search): void {
                $digits = preg_replace('/\D/', '', $search);

                $query->where(function (Builder $query) use ($search, $digits): void {
                    $query->where('legal_name', 'like', "%{$search}%")
                        ->orWhere('trade_name', 'like', "%{$search}%")
                        ->when($digits !== '', fn (Builder $query) => $query->orWhere('document', 'like', "{$digits}%"));
                });
            })
            ->orderBy('legal_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, B2bClient>
     */
    public function all(): Collection
    {
        return B2bClient::query()
            ->orderBy('legal_name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): B2bClient
    {
        return B2bClient::create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(B2bClient $client, array $attributes): B2bClient
    {
        $client->update($attributes);

        return $client;
    }

    public function delete(B2bClient $client): void
    {
        $client->delete();
    }
}

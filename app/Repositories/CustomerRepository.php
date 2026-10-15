<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * As consultas já saem filtradas pela empresa atual (BelongsToTenant).
 */
class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Customer::query()
            ->when($search, function (Builder $query, string $search): void {
                $digits = preg_replace('/\D/', '', $search);

                // Agrupado para o OR não escapar do filtro da empresa.
                $query->where(function (Builder $query) use ($search, $digits): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('trade_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->when($digits !== '', fn (Builder $query) => $query->orWhere('document', 'like', "{$digits}%"));
                });
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Customer
    {
        return Customer::create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Customer $customer, array $attributes): Customer
    {
        $customer->update($attributes);

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    public function loadDetails(Customer $customer): Customer
    {
        return $customer->load([
            'addresses' => fn ($query) => $query->orderBy('id'),
            'contacts' => fn ($query) => $query->orderBy('name')->orderBy('id'),
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $addresses
     */
    public function syncAddresses(Customer $customer, array $addresses): void
    {
        $this->syncChildren($customer, 'addresses', $addresses);
    }

    /**
     * @param  list<array<string, mixed>>  $contacts
     */
    public function syncContacts(Customer $customer, array $contacts): void
    {
        $this->syncChildren($customer, 'contacts', $contacts);
    }

    /**
     * Os ids já foram validados como do cliente. A relação é recriada a cada
     * consulta porque os métodos de busca alteram a query dela.
     *
     * @param  'addresses'|'contacts'  $relation
     * @param  list<array<string, mixed>>  $rows
     */
    private function syncChildren(Customer $customer, string $relation, array $rows): void
    {
        $keptIds = [];

        foreach ($rows as $row) {
            $attributes = Arr::except($row, 'id');

            if (empty($row['id'])) {
                $keptIds[] = $customer->{$relation}()->create($attributes)->id;

                continue;
            }

            $child = $customer->{$relation}()->findOrFail($row['id']);
            $child->update($attributes);
            $keptIds[] = $child->id;
        }

        // Um a um, para cada exclusão ir para o histórico.
        $customer->{$relation}()->whereKeyNot($keptIds)->get()->each->delete();
    }
}

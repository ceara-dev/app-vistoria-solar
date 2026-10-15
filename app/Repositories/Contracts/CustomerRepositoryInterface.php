<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Customer;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Customer $customer, array $attributes): Customer;

    public function delete(Customer $customer): void;

    public function loadDetails(Customer $customer): Customer;

    /**
     * Deixa o cliente com exatamente os endereços informados: atualiza os que
     * têm `id`, cria os sem `id` e apaga os que ficaram de fora.
     *
     * @param  list<array<string, mixed>>  $addresses
     */
    public function syncAddresses(Customer $customer, array $addresses): void;

    /**
     * Mesma lógica de `syncAddresses()`, para os contatos.
     *
     * @param  list<array<string, mixed>>  $contacts
     */
    public function syncContacts(Customer $customer, array $contacts): void;
}

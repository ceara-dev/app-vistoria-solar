<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Clientes da empresa, com endereços e contatos.
 */
class CustomerService
{
    public const PER_PAGE = 15;

    public function __construct(
        protected CustomerRepositoryInterface $repository,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(?string $search): LengthAwarePaginator
    {
        return $this->repository->paginate($search, self::PER_PAGE);
    }

    public function details(Customer $customer): Customer
    {
        return $this->repository->loadDetails($customer);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Customer
    {
        return DB::transaction(function () use ($data): Customer {
            $customer = $this->repository->create($this->customerAttributes($data));
            $this->syncChildren($customer, $data);

            return $customer;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data): Customer {
            $this->repository->update($customer, $this->customerAttributes($data));
            $this->syncChildren($customer, $data);

            return $customer;
        });
    }

    public function delete(Customer $customer): void
    {
        $this->repository->delete($customer);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function customerAttributes(array $data): array
    {
        return Arr::except($data, ['addresses', 'contacts']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncChildren(Customer $customer, array $data): void
    {
        $this->repository->syncAddresses($customer, $data['addresses'] ?? []);
        $this->repository->syncContacts($customer, $data['contacts'] ?? []);
    }
}

<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\BrazilianState;
use App\Enums\CustomerType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Customer\IndexCustomerRequest;
use App\Http\Requests\Tenant\Customer\StoreCustomerRequest;
use App\Http\Requests\Tenant\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Autorização: index, store e update pelos FormRequests; as demais pela rota.
 */
class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $service,
    ) {}

    public function index(IndexCustomerRequest $request): Response
    {
        $search = $request->validated('search');

        return Inertia::render('Tenant/Customers/Index', [
            'customers' => CustomerResource::collection($this->service->paginate($search)),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tenant/Customers/Create', $this->formOptions());
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = $this->service->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => "Cliente {$customer->name} cadastrado."]);

        return to_route('tenant.customers.show', $customer);
    }

    public function show(Customer $customer): Response
    {
        return Inertia::render('Tenant/Customers/Show', [
            'customer' => new CustomerResource($this->service->details($customer)),
        ]);
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('Tenant/Customers/Edit', [
            'customer' => new CustomerResource($this->service->details($customer)),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->service->update($customer, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => "Cliente {$customer->name} atualizado."]);

        return to_route('tenant.customers.show', $customer);
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->service->delete($customer);

        Inertia::flash('toast', ['type' => 'success', 'message' => "Cliente {$customer->name} excluído."]);

        return to_route('tenant.customers.index');
    }

    /**
     * @return array{types: list<array{value: string, label: string}>, states: list<array{value: string, label: string}>}
     */
    private function formOptions(): array
    {
        return [
            'types' => CustomerType::options(),
            'states' => BrazilianState::options(),
        ];
    }
}

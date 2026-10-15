<?php

use App\Enums\CustomerType;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantRole;
use App\Tenancy\TenantScope;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Cadastro válido de pessoa física com um endereço e um contato.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function customerPayload(array $overrides = []): array
{
    return array_replace([
        'type' => 'PF',
        'name' => 'Maria Oliveira',
        'trade_name' => null,
        'document' => '529.982.247-25',
        'email' => 'Maria@Example.com',
        'phone' => '(11) 98765-4321',
        'notes' => null,
        'addresses' => [[
            'id' => null,
            'label' => 'Residência',
            'zip_code' => '01310-100',
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => null,
            'district' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]],
        'contacts' => [[
            'id' => null,
            'name' => 'João Oliveira',
            'position' => 'Cônjuge',
            'email' => null,
            'phone' => '1133334444',
        ]],
    ], $overrides);
}

/**
 * Lê um cliente de qualquer empresa, inclusive excluído.
 */
function findCustomer(int $id): ?Customer
{
    $withoutTenantScope = fn ($query) => $query->withoutGlobalScope(TenantScope::class);

    return Customer::withoutGlobalScope(TenantScope::class)
        ->withTrashed()
        ->with(['addresses' => $withoutTenantScope, 'contacts' => $withoutTenantScope])
        ->find($id);
}

describe('index', function () {
    it('lists only the customers of the current tenant, by name', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();
        Customer::factory()->for($tenant)->create(['name' => 'Bruno']);
        Customer::factory()->for($tenant)->create(['name' => 'Ana']);
        Customer::factory()->create(['name' => 'Cliente de outra empresa']);

        $this->actingAs($user)
            ->get(route('tenant.customers.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Customers/Index')
                ->has('customers.data', 2)
                ->where('customers.data.0.name', 'Ana')
                ->where('customers.data.1.name', 'Bruno'));
    });

    it('finds customers by name or by the digits of the document', function (string $search) {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        Customer::factory()->for($tenant)->create(['name' => 'Maria Oliveira', 'document' => '52998224725']);
        Customer::factory()->for($tenant)->create(['name' => 'José Souza']);

        $this->actingAs($user)
            ->get(route('tenant.customers.index', ['search' => $search]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('customers.data', 1)
                ->where('customers.data.0.name', 'Maria Oliveira')
                ->where('filters.search', $search));
    })->with(['by name' => 'oliveira', 'by formatted document' => '529.982']);

    it('does not bring customers of another tenant into the search', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        Customer::factory()->create(['name' => 'Maria de outra empresa']);

        $this->actingAs($user)
            ->get(route('tenant.customers.index', ['search' => 'Maria']))
            ->assertInertia(fn (Assert $page) => $page->has('customers.data', 0));
    });

    it('paginates 15 customers per page', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        Customer::factory()->for($tenant)->count(16)->create();

        $this->actingAs($user)
            ->get(route('tenant.customers.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('customers.data', 15)
                ->where('customers.meta.total', 16)
                ->where('customers.meta.last_page', 2));
    });
});

describe('store', function () {
    it('creates the customer with addresses and contacts in the current tenant', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();

        $response = $this->actingAs($user)->post(route('tenant.customers.store'), customerPayload());

        $customer = Customer::withoutGlobalScope(TenantScope::class)->sole();
        $response->assertRedirect(route('tenant.customers.show', $customer))
            ->assertInertiaFlash('toast.type', 'success');

        $customer = findCustomer($customer->id);

        expect($customer->tenant_id)->toBe($tenant->id)
            ->and($customer->type)->toBe(CustomerType::Individual)
            ->and($customer->document)->toBe('52998224725')
            ->and($customer->phone)->toBe('11987654321')
            ->and($customer->email)->toBe('maria@example.com')
            ->and($customer->addresses->sole()->only('tenant_id', 'zip_code', 'street'))->toBe([
                'tenant_id' => $tenant->id,
                'zip_code' => '01310100',
                'street' => 'Avenida Paulista',
            ])
            ->and($customer->contacts->sole()->only('tenant_id', 'name', 'phone'))->toBe([
                'tenant_id' => $tenant->id,
                'name' => 'João Oliveira',
                'phone' => '1133334444',
            ]);
    });

    it('validates the document by the customer type', function (string $type, string $document, string $message) {
        $user = User::factory()->forTenant(Tenant::factory()->create())->create();

        $this->actingAs($user)
            ->post(route('tenant.customers.store'), customerPayload(['type' => $type, 'document' => $document]))
            ->assertSessionHasErrors(['document' => $message]);
    })->with([
        'individual with an invalid CPF' => ['PF', '529.982.247-24', 'CPF inválido'],
        'company with a CPF' => ['PJ', '529.982.247-25', 'CNPJ inválido'],
        'text without digits' => ['PF', 'não sei', 'CPF inválido'],
    ]);

    it('rejects a document already used by a customer of the tenant', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        Customer::factory()->for($tenant)->create(['document' => '52998224725']);

        $this->actingAs($user)
            ->post(route('tenant.customers.store'), customerPayload())
            ->assertSessionHasErrors(['document' => 'Este valor já está em uso']);
    });

    it('accepts a document used only by a customer of another tenant', function () {
        $user = User::factory()->forTenant(Tenant::factory()->create())->create();
        Customer::factory()->create(['document' => '52998224725']);

        $this->actingAs($user)
            ->post(route('tenant.customers.store'), customerPayload())
            ->assertSessionHasNoErrors();
    });

    it('validates the addresses and contacts with the contract messages', function () {
        $user = User::factory()->forTenant(Tenant::factory()->create())->create();
        $payload = customerPayload();
        $payload['addresses'][0]['street'] = null;
        $payload['addresses'][0]['zip_code'] = '0131010';
        $payload['addresses'][0]['state'] = 'XX';
        $payload['contacts'][0]['phone'] = '987654321';

        $this->actingAs($user)
            ->post(route('tenant.customers.store'), $payload)
            ->assertSessionHasErrors([
                'addresses.0.street' => 'Este campo é obrigatório',
                'addresses.0.zip_code' => 'CEP inválido',
                'addresses.0.state' => 'O valor selecionado é inválido',
                'contacts.0.phone' => 'Telefone inválido',
            ]);
    });

    it('rejects an existing address id when creating', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $address = CustomerAddress::factory()->for(Customer::factory()->for($tenant))->create();
        $payload = customerPayload();
        $payload['addresses'][0]['id'] = $address->id;

        $this->actingAs($user)
            ->post(route('tenant.customers.store'), $payload)
            ->assertSessionHasErrors(['addresses.0.id' => 'O valor selecionado é inválido']);
    });

    it('forbids members who can only view customers', function () {
        $user = User::factory()->forTenant(Tenant::factory()->create(), TenantRole::Inspector)->create();

        $this->actingAs($user)->get(route('tenant.customers.create'))->assertForbidden();
        $this->actingAs($user)->post(route('tenant.customers.store'), customerPayload())->assertForbidden();

        expect(Customer::withoutGlobalScope(TenantScope::class)->count())->toBe(0);
    });
});

describe('update', function () {
    it('updates the customer and keeps exactly the addresses and contacts sent', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create(['document' => '52998224725']);
        $keptAddress = CustomerAddress::factory()->for($customer)->create(['street' => 'Rua Antiga']);
        $removedAddress = CustomerAddress::factory()->for($customer)->create();
        CustomerContact::factory()->for($customer)->create();

        $payload = customerPayload(['name' => 'Maria Atualizada', 'contacts' => []]);
        $payload['addresses'] = [
            [...$payload['addresses'][0], 'id' => $keptAddress->id, 'street' => 'Rua Nova'],
            [...$payload['addresses'][0], 'label' => 'Usina'],
        ];

        $this->actingAs($user)
            ->put(route('tenant.customers.update', $customer), $payload)
            ->assertRedirect(route('tenant.customers.show', $customer));

        $customer = findCustomer($customer->id);

        expect($customer->name)->toBe('Maria Atualizada')
            ->and($customer->addresses->pluck('street', 'id')->all())->toHaveKey($keptAddress->id, 'Rua Nova')
            ->and($customer->addresses->pluck('id')->all())->not->toContain($removedAddress->id)
            ->and($customer->addresses)->toHaveCount(2)
            ->and($customer->contacts)->toBeEmpty();
    });

    it('records the removed address in the tenant history', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create();
        $address = CustomerAddress::factory()->for($customer)->create();

        $this->actingAs($user)->put(route('tenant.customers.update', $customer), customerPayload(['addresses' => [], 'contacts' => []]));

        $deletion = auditsOf($tenant, 'deleted')->firstWhere('auditable_id', $address->id);

        expect($deletion->auditable_type)->toBe(CustomerAddress::class)
            ->and($deletion->user_id)->toBe($user->id);
    });

    it('rejects an address id of another customer of the same tenant', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create();
        $otherCustomerAddress = CustomerAddress::factory()->for(Customer::factory()->for($tenant))->create();
        $payload = customerPayload();
        $payload['addresses'][0]['id'] = $otherCustomerAddress->id;

        $this->actingAs($user)
            ->put(route('tenant.customers.update', $customer), $payload)
            ->assertSessionHasErrors(['addresses.0.id' => 'O valor selecionado é inválido']);
    });

    it('accepts the customer keeping its own document', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create(['document' => '52998224725']);

        $this->actingAs($user)
            ->put(route('tenant.customers.update', $customer), customerPayload())
            ->assertSessionHasNoErrors();
    });

    it('forbids members who can only view customers', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();
        $customer = Customer::factory()->for($tenant)->create();

        $this->actingAs($user)->get(route('tenant.customers.edit', $customer))->assertForbidden();
        $this->actingAs($user)->put(route('tenant.customers.update', $customer), customerPayload())->assertForbidden();
    });
});

describe('destroy', function () {
    it('moves the customer to the trash', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create();

        $this->actingAs($user)
            ->delete(route('tenant.customers.destroy', $customer))
            ->assertRedirect(route('tenant.customers.index'));

        expect(findCustomer($customer->id)->trashed())->toBeTrue();
    });

    it('forbids members who can only view customers', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();
        $customer = Customer::factory()->for($tenant)->create();

        $this->actingAs($user)->delete(route('tenant.customers.destroy', $customer))->assertForbidden();

        expect(findCustomer($customer->id)->trashed())->toBeFalse();
    });
});

describe('show', function () {
    it('shows the customer with addresses and contacts', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant, TenantRole::Inspector)->create();
        $customer = Customer::factory()->for($tenant)->create(['name' => 'Maria']);
        CustomerAddress::factory()->for($customer)->create(['city' => 'Campinas']);
        CustomerContact::factory()->for($customer)->create(['name' => 'João']);

        $this->actingAs($user)
            ->get(route('tenant.customers.show', $customer))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Customers/Show')
                ->where('customer.data.name', 'Maria')
                ->where('customer.data.addresses.0.city', 'Campinas')
                ->where('customer.data.contacts.0.name', 'João'));
    });
});

it('returns 404 for a customer of another tenant on every record route', function (string $method, string $routeName) {
    $user = User::factory()->forTenant(Tenant::factory()->create(), TenantRole::Owner)->create();
    $otherTenantCustomer = Customer::factory()->create(['name' => 'Intocado']);

    $this->actingAs($user)
        ->call(strtoupper($method), route($routeName, $otherTenantCustomer), customerPayload(['name' => 'Alterado']))
        ->assertNotFound();

    $customer = findCustomer($otherTenantCustomer->id);

    expect($customer->name)->toBe('Intocado')
        ->and($customer->trashed())->toBeFalse();
})->with('customer record routes');

<?php

use App\Models\B2bClient;
use App\Models\Tenant;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function b2bClientPayload(array $overrides = []): array
{
    return array_replace([
        'legal_name' => 'Solar Brasil LTDA',
        'trade_name' => 'Solar Brasil',
        'document' => '11.222.333/0001-81',
        'contact_name' => 'João Souza',
        'contact_email' => 'Joao@Example.com',
        'contact_phone' => '(11) 98765-4321',
    ], $overrides);
}

describe('index', function () {
    it('lists the clients with the number of companies', function () {
        actingAsCentralAdmin();

        $client = B2bClient::factory()->create(['legal_name' => 'Alpha LTDA', 'trade_name' => 'Alpha']);
        Tenant::factory()->count(2)->for($client, 'b2bClient')->create();
        Tenant::factory()->create();
        B2bClient::factory()->create(['legal_name' => 'Beta LTDA', 'trade_name' => 'Beta']);

        $this->get(route('central.b2b-clients.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Central/B2bClients/Index')
                ->has('clients.data', 2)
                ->where('clients.data.0.trade_name', 'Alpha')
                ->where('clients.data.0.tenants_count', 2)
                ->where('clients.data.1.tenants_count', 0));
    });

    it('searches by name or by the digits of the document', function (string $search) {
        actingAsCentralAdmin();

        B2bClient::factory()->create(['legal_name' => 'Solaris LTDA', 'trade_name' => 'Solaris', 'document' => '11222333000181']);
        B2bClient::factory()->create(['legal_name' => 'Outra LTDA', 'trade_name' => 'Outra', 'document' => '04252011000110']);

        $this->get(route('central.b2b-clients.index', ['search' => $search]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('clients.data', 1)
                ->where('clients.data.0.trade_name', 'Solaris')
                ->where('filters.search', $search));
    })->with(['by name' => 'solar', 'by formatted document' => '11.222.333']);

    it('paginates 15 clients per page', function () {
        actingAsCentralAdmin();

        B2bClient::factory()->count(16)->create();

        $this->get(route('central.b2b-clients.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('clients.data', 15)
                ->where('clients.meta.total', 16));
    });
});

describe('store', function () {
    it('creates the client with the normalized contact data', function () {
        actingAsCentralAdmin();

        $this->post(route('central.b2b-clients.store'), b2bClientPayload())
            ->assertRedirect(route('central.b2b-clients.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $client = B2bClient::sole();

        expect($client->legal_name)->toBe('Solar Brasil LTDA')
            ->and($client->trade_name)->toBe('Solar Brasil')
            ->and($client->document)->toBe('11222333000181')
            ->and($client->contact_email)->toBe('joao@example.com')
            ->and($client->contact_phone)->toBe('11987654321');
    });

    it('accepts a client without document and contact', function () {
        actingAsCentralAdmin();

        $this->post(route('central.b2b-clients.store'), b2bClientPayload([
            'trade_name' => null,
            'document' => null,
            'contact_name' => null,
            'contact_email' => null,
            'contact_phone' => null,
        ]))->assertSessionHasNoErrors();

        $client = B2bClient::sole();

        expect($client->document)->toBeNull()
            ->and($client->contact_email)->toBeNull();
    });

    it('validates the required data, the CNPJ and the contact', function (array $overrides, string $field, string $message) {
        actingAsCentralAdmin();

        $this->post(route('central.b2b-clients.store'), b2bClientPayload($overrides))
            ->assertSessionHasErrors([$field => $message]);

        expect(B2bClient::count())->toBe(0);
    })->with([
        'legal name missing' => [['legal_name' => null], 'legal_name', 'Este campo é obrigatório'],
        'invalid cnpj' => [['document' => '11.222.333/0001-82'], 'document', 'CNPJ inválido'],
        'text without digits' => [['document' => 'não sei'], 'document', 'CNPJ inválido'],
        'invalid email' => [['contact_email' => 'joao@'], 'contact_email', 'E-mail inválido'],
        'invalid phone' => [['contact_phone' => '987654321'], 'contact_phone', 'Telefone inválido'],
    ]);

    it('rejects a CNPJ already used by another client', function () {
        actingAsCentralAdmin();
        B2bClient::factory()->create(['document' => '11222333000181']);

        $this->post(route('central.b2b-clients.store'), b2bClientPayload())
            ->assertSessionHasErrors(['document' => 'Este valor já está em uso']);
    });
});

describe('update', function () {
    it('updates the client keeping its own CNPJ', function () {
        actingAsCentralAdmin();

        $client = B2bClient::factory()->create(['document' => '11222333000181']);

        $this->put(route('central.b2b-clients.update', $client), b2bClientPayload([
            'trade_name' => 'Solar Brasil Novo',
        ]))->assertRedirect(route('central.b2b-clients.index'));

        $client->refresh();

        expect($client->trade_name)->toBe('Solar Brasil Novo')
            ->and($client->legal_name)->toBe('Solar Brasil LTDA');
    });

    it('rejects the CNPJ of another client', function () {
        actingAsCentralAdmin();

        B2bClient::factory()->create(['document' => '11222333000181']);
        $client = B2bClient::factory()->create(['document' => '04252011000110']);

        $this->put(route('central.b2b-clients.update', $client), b2bClientPayload())
            ->assertSessionHasErrors(['document' => 'Este valor já está em uso']);
    });

    it('shows the client on the edit screen', function () {
        actingAsCentralAdmin();

        $client = B2bClient::factory()->create(['legal_name' => 'Solar Brasil LTDA', 'trade_name' => 'Solar Brasil']);

        $this->get(route('central.b2b-clients.edit', $client))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Central/B2bClients/Edit')
                ->where('client.data.legal_name', 'Solar Brasil LTDA')
                ->where('client.data.trade_name', 'Solar Brasil'));
    });
});

describe('destroy', function () {
    it('deletes the client and unlinks the companies', function () {
        actingAsCentralAdmin();

        $client = B2bClient::factory()->create();
        $tenant = Tenant::factory()->for($client, 'b2bClient')->create();

        $this->delete(route('central.b2b-clients.destroy', $client))
            ->assertRedirect(route('central.b2b-clients.index'));

        expect(B2bClient::count())->toBe(0)
            ->and($tenant->refresh()->b2b_client_id)->toBeNull();
    });
});

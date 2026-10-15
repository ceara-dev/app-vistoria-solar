<?php

use App\Models\Customer;
use App\Models\Tenant;
use App\Repositories\Central\Contracts\TenantRepositoryInterface;

describe('customersCountByTenant', function () {
    it('counts the customers of each company without the tenant scope', function () {
        $alpha = Tenant::factory()->create();
        $beta = Tenant::factory()->create();
        Customer::factory()->for($alpha)->count(3)->create();
        Customer::factory()->for($beta)->count(2)->create();

        // Sem empresa no contexto, exatamente como a Central roda.
        $counts = app(TenantRepositoryInterface::class)->customersCountByTenant([$alpha->id, $beta->id]);

        expect($counts)->toBe([$alpha->id => 3, $beta->id => 2]);
    });

    it('does not count the customers in the trash', function () {
        $tenant = Tenant::factory()->create();
        Customer::factory()->for($tenant)->count(3)->create();
        Customer::factory()->for($tenant)->create()->delete();

        expect(app(TenantRepositoryInterface::class)->customersCountByTenant([$tenant->id]))->toBe([$tenant->id => 3]);
    });

    it('returns nothing for an empty list of companies', function () {
        expect(app(TenantRepositoryInterface::class)->customersCountByTenant([]))->toBe([]);
    });
});

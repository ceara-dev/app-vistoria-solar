<?php

namespace Database\Seeders;

use App\Models\B2bClient;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Sem eventos de model, o BelongsToTenant não preenche `tenant_id`: os
     * dados de empresa precisam de `for($tenant)`.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(PlansSeeder::class);

        User::factory()->centralAdmin()->create([
            'name' => 'Equipe Central',
            'email' => 'central@example.com',
        ]);

        $b2bClient = B2bClient::factory()->create([
            'legal_name' => 'Cliente B2B Demonstração LTDA',
            'trade_name' => 'Cliente Demonstração',
        ]);

        $tenant = Tenant::factory()
            ->onPlan(Plan::where('name', 'Padrão')->firstOrFail())
            ->for($b2bClient, 'b2bClient')
            ->create(['name' => 'Empresa Demonstração']);

        User::factory()->forTenant($tenant, TenantRole::Owner)->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Customer::factory()
            ->for($tenant)
            ->count(8)
            ->has(CustomerAddress::factory(), 'addresses')
            ->has(CustomerContact::factory(), 'contacts')
            ->create();

        Customer::factory()
            ->company()
            ->for($tenant)
            ->count(4)
            ->has(CustomerAddress::factory()->count(2), 'addresses')
            ->create();
    }
}

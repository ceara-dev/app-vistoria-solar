<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Planos de exemplo da plataforma. Pode rodar mais de uma vez.
 *
 * Os limites ainda não são fiscalizados (decisão de 15/10/2026): existem
 * para a Central montar os planos quando a cobrança entrar.
 */
class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::updateOrCreate(
            ['name' => 'Padrão'],
            [
                'price_cents' => 0,
                'limits' => ['users' => 5, 'trips_per_month' => 100, 'storage_mb' => 1024],
                'is_active' => true,
            ],
        );

        Plan::updateOrCreate(
            ['name' => 'Profissional'],
            [
                'price_cents' => 19900,
                'limits' => ['users' => 20, 'trips_per_month' => 1000, 'storage_mb' => 10240],
                'is_active' => true,
            ],
        );
    }
}

<?php

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('is rejected by the database when linked to a customer of another tenant', function () {
    $customer = Customer::factory()->create();
    $otherTenant = Tenant::factory()->create();

    // Por fora do Eloquent de propósito: é a última barreira, se a validação falhar.
    $insertAddress = fn () => DB::table('customer_addresses')->insert([
        'tenant_id' => $otherTenant->id,
        'customer_id' => $customer->id,
        'zip_code' => '01310100',
        'street' => 'Avenida Paulista',
        'number' => '1000',
        'district' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
    ]);

    expect($insertAddress)->toThrow(QueryException::class);
});

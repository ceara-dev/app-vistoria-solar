<?php

use App\Models\B2bClient;
use App\Models\Tenant;

it('has the tenants linked to the client', function () {
    $client = B2bClient::factory()->create();
    Tenant::factory()->count(2)->for($client, 'b2bClient')->create();
    Tenant::factory()->create();

    expect($client->tenants()->count())->toBe(2);
});

it('displays the trade name when it exists and the legal name otherwise', function () {
    $withTradeName = B2bClient::factory()->make(['legal_name' => 'Razão LTDA', 'trade_name' => 'Fantasia']);
    $withoutTradeName = B2bClient::factory()->make(['legal_name' => 'Razão LTDA', 'trade_name' => null]);

    expect($withTradeName->displayName())->toBe('Fantasia')
        ->and($withoutTradeName->displayName())->toBe('Razão LTDA');
});

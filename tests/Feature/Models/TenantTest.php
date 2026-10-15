<?php

use App\Models\Tenant;

it('reports whether members may use the system', function (Tenant $tenant, bool $allowsAccess) {
    expect($tenant->allowsAccess())->toBe($allowsAccess);
})->with([
    'active' => [fn () => Tenant::factory()->make(), true],
    'trial in progress' => [fn () => Tenant::factory()->trial()->make(), true],
    'trial without end date' => [fn () => new Tenant, true],
    'trial expired' => [fn () => Tenant::factory()->expiredTrial()->make(), false],
    'suspended' => [fn () => Tenant::factory()->suspended()->make(), false],
    'canceled' => [fn () => Tenant::factory()->canceled()->make(), false],
]);

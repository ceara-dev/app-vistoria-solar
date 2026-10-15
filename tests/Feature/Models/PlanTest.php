<?php

use App\Models\Plan;
use App\Models\Tenant;

it('casts its attributes', function () {
    $plan = Plan::factory()->create([
        'price_cents' => 19900,
        'limits' => ['users' => 5, 'trips_per_month' => null],
        'is_active' => true,
    ])->refresh();

    expect($plan->price_cents)->toBeInt()->toBe(19900)
        ->and($plan->limits)->toBe(['users' => 5, 'trips_per_month' => null])
        ->and($plan->is_active)->toBeTrue();
});

it('has the tenants on the plan', function () {
    $plan = Plan::factory()->create();
    Tenant::factory()->count(2)->onPlan($plan)->create();
    Tenant::factory()->create();

    expect($plan->tenants()->count())->toBe(2);
});

it('starts active and can be created inactive', function () {
    expect(Plan::factory()->create()->is_active)->toBeTrue()
        ->and(Plan::factory()->inactive()->create()->is_active)->toBeFalse();
});

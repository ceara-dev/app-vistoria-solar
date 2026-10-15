<?php

use App\Models\Plan;
use Database\Seeders\PlansSeeder;

it('creates the example plans with their limits', function () {
    $this->seed(PlansSeeder::class);

    $default = Plan::where('name', 'Padrão')->sole();
    $professional = Plan::where('name', 'Profissional')->sole();

    expect(Plan::count())->toBe(2)
        ->and($default->is_active)->toBeTrue()
        ->and($default->limits)->toHaveKey('users')
        ->and($professional->price_cents)->toBeGreaterThan(0);
});

it('can run again without duplicating plans', function () {
    $this->seed(PlansSeeder::class);
    $this->seed(PlansSeeder::class);

    expect(Plan::count())->toBe(2);
});

<?php

use App\Models\Plan;
use App\Models\Tenant;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the platform indicators', function () {
    actingAsCentralAdmin();

    Tenant::factory()->count(2)->create();
    Tenant::factory()->trial()->create();
    Tenant::factory()->suspended()->create();
    Tenant::factory()->canceled()->create();
    Plan::factory()->count(2)->create();

    $this->get(route('central.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Central/Dashboard/Index')
            ->where('stats.tenants.total', 5)
            ->where('stats.tenants.active', 2)
            ->where('stats.tenants.trial', 1)
            ->where('stats.tenants.suspended', 1)
            ->where('stats.tenants.canceled', 1)
            ->where('stats.plans', 2));
});

it('shows zeroed indicators without companies', function () {
    actingAsCentralAdmin();

    $this->get(route('central.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.tenants.total', 0)
            ->where('stats.tenants.active', 0)
            ->where('stats.tenants.trial', 0)
            ->where('stats.plans', 0));
});

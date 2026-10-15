<?php

use App\Models\Tenant;
use App\Models\User;

it('does not publish the central routes on company pages', function () {
    $user = User::factory()->forTenant(Tenant::factory()->create())->create();

    $this->actingAs($user)
        ->get(route('tenant.dashboard'))
        ->assertOk()
        ->assertSee('tenant.dashboard', false)
        ->assertDontSee('central.tenants.index', false);
});

it('publishes the central routes on central pages', function () {
    $this->actingAs(User::factory()->centralAdmin()->create())
        ->get(route('central.dashboard'))
        ->assertOk()
        ->assertSee('central.tenants.index', false);
});

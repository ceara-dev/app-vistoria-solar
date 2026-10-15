<?php

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantRole;

it('redirects guests to the login screen', function () {
    $this->get(route('central.dashboard'))->assertRedirect(route('login'));
});

it('forbids signed in users who are not from the central team', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('central.dashboard'))
        ->assertForbidden();
});

it('forbids a company owner, even with every permission', function () {
    $user = User::factory()->forTenant(Tenant::factory()->create(), TenantRole::Owner)->create();

    $this->actingAs($user)->get(route('central.dashboard'))->assertForbidden();
});

it('lets the central team in', function () {
    $admin = User::factory()->centralAdmin()->create();

    $this->actingAs($admin)->get(route('central.dashboard'))->assertOk();
});

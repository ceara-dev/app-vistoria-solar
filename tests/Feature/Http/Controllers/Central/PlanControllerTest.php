<?php

use App\Models\Plan;
use App\Models\Tenant;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function centralPlanPayload(array $overrides = []): array
{
    return array_replace([
        'name' => 'Profissional',
        'price_cents' => 19900,
        'limits' => [
            'users' => 20,
            'trips_per_month' => 1000,
            'storage_mb' => 10240,
        ],
        'is_active' => true,
    ], $overrides);
}

describe('index', function () {
    it('lists the plans with the number of companies', function () {
        actingAsCentralAdmin();

        $plan = Plan::factory()->create(['name' => 'Padrão']);
        Tenant::factory()->count(2)->onPlan($plan)->create();
        Tenant::factory()->create();

        $this->get(route('central.plans.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Central/Plans/Index')
                ->has('plans.data', 1)
                ->where('plans.data.0.name', 'Padrão')
                ->where('plans.data.0.tenants_count', 2));
    });
});

describe('store', function () {
    it('creates the plan with its limits', function () {
        actingAsCentralAdmin();

        $this->post(route('central.plans.store'), centralPlanPayload())
            ->assertRedirect(route('central.plans.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $plan = Plan::sole();

        expect($plan->name)->toBe('Profissional')
            ->and($plan->price_cents)->toBe(19900)
            ->and($plan->limits)->toBe(['users' => 20, 'trips_per_month' => 1000, 'storage_mb' => 10240])
            ->and($plan->is_active)->toBeTrue();
    });

    it('saves a plan without limits as unlimited', function () {
        actingAsCentralAdmin();

        $this->post(route('central.plans.store'), centralPlanPayload([
            'price_cents' => 0,
            'limits' => ['users' => null, 'trips_per_month' => null, 'storage_mb' => null],
        ]))->assertSessionHasNoErrors();

        expect(Plan::sole()->limits)->toBeNull();
    });

    it('validates the name, the price and the limits', function (array $overrides, string $field) {
        actingAsCentralAdmin();

        $this->post(route('central.plans.store'), centralPlanPayload($overrides))
            ->assertSessionHasErrors($field);

        expect(Plan::count())->toBe(0);
    })->with([
        'name missing' => [['name' => null], 'name'],
        'price negative' => [['price_cents' => -1], 'price_cents'],
        'price not integer' => [['price_cents' => 'muito'], 'price_cents'],
        'users limit zero' => [['limits' => ['users' => 0, 'trips_per_month' => null, 'storage_mb' => null]], 'limits.users'],
        'storage limit not integer' => [['limits' => ['users' => null, 'trips_per_month' => null, 'storage_mb' => '10GB']], 'limits.storage_mb'],
    ]);

    it('rejects a name already used by another plan', function () {
        actingAsCentralAdmin();
        Plan::factory()->create(['name' => 'Profissional']);

        $this->post(route('central.plans.store'), centralPlanPayload())
            ->assertSessionHasErrors(['name' => 'Este valor já está em uso']);
    });
});

describe('update', function () {
    it('updates the plan and deactivates it', function () {
        actingAsCentralAdmin();

        $plan = Plan::factory()->create(['name' => 'Padrão']);

        $this->put(route('central.plans.update', $plan), centralPlanPayload([
            'name' => 'Padrão Novo',
            'price_cents' => 9900,
            'limits' => ['users' => null, 'trips_per_month' => 50, 'storage_mb' => null],
            'is_active' => false,
        ]))->assertRedirect(route('central.plans.index'));

        $plan->refresh();

        expect($plan->name)->toBe('Padrão Novo')
            ->and($plan->price_cents)->toBe(9900)
            ->and($plan->limits)->toBe(['trips_per_month' => 50])
            ->and($plan->is_active)->toBeFalse();
    });

    it('accepts the plan keeping its own name', function () {
        actingAsCentralAdmin();

        $plan = Plan::factory()->create(['name' => 'Padrão']);

        $this->put(route('central.plans.update', $plan), centralPlanPayload(['name' => 'Padrão']))
            ->assertSessionHasNoErrors();
    });

    it('rejects the name of another plan', function () {
        actingAsCentralAdmin();

        Plan::factory()->create(['name' => 'Padrão']);
        $plan = Plan::factory()->create(['name' => 'Profissional']);

        $this->put(route('central.plans.update', $plan), centralPlanPayload(['name' => 'Padrão']))
            ->assertSessionHasErrors('name');
    });

    it('shows the plan on the edit screen', function () {
        actingAsCentralAdmin();

        $plan = Plan::factory()->create(['name' => 'Padrão', 'price_cents' => 19900]);

        $this->get(route('central.plans.edit', $plan))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Central/Plans/Edit')
                ->where('plan.data.name', 'Padrão')
                ->where('plan.data.price_cents', 19900));
    });
});

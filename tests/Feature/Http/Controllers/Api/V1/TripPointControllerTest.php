<?php

use App\Models\Tenant;
use App\Models\Trip;
use App\Models\TripPoint;
use App\Models\User;
use App\Tenancy\TenantScope;
use Illuminate\Support\Str;

/**
 * @return list<array<string, mixed>>
 */
function tripPoints(int $count): array
{
    return array_map(fn (int $index): array => [
        'id' => (string) Str::uuid7(),
        'latitude' => -23.5613 + $index / 10000,
        'longitude' => -46.6565,
        'recorded_at' => now()->addSeconds($index)->toIso8601String(),
        'status' => $index % 2 === 0 ? 'M' : 'P',
        'speed_ms' => 8.5,
    ], range(1, $count));
}

it('stores a batch of points in the trip and the tenant of the token', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $trip = Trip::factory()->for($tenant)->for($user)->create();

    $this->withToken(apiTokenFor($user, $tenant))
        ->postJson(route('api.v1.trips.points.store', $trip), ['points' => tripPoints(3)])
        ->assertOk()
        ->assertJsonPath('data', ['received' => 3, 'stored' => 3]);

    $points = TripPoint::withoutGlobalScope(TenantScope::class)->get();

    expect($points)->toHaveCount(3)
        ->and($points->pluck('tenant_id')->unique()->all())->toBe([$tenant->id])
        ->and($points->pluck('trip_id')->unique()->all())->toBe([$trip->id]);
});

it('ignores points that were already received', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $trip = Trip::factory()->for($tenant)->for($user)->create();
    $points = tripPoints(3);
    $token = apiTokenFor($user, $tenant);
    $this->withToken($token)->postJson(route('api.v1.trips.points.store', $trip), ['points' => $points]);

    $this->withToken($token)
        ->postJson(route('api.v1.trips.points.store', $trip), ['points' => [...$points, ...tripPoints(1)]])
        ->assertJsonPath('data', ['received' => 4, 'stored' => 1]);

    expect(TripPoint::withoutGlobalScope(TenantScope::class)->count())->toBe(4);
});

it('answers 404 for a trip of another tenant', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $otherTenantTrip = Trip::factory()->create();

    $this->withToken(apiTokenFor($user, $tenant))
        ->postJson(route('api.v1.trips.points.store', $otherTenantTrip), ['points' => tripPoints(1)])
        ->assertNotFound();

    expect(TripPoint::withoutGlobalScope(TenantScope::class)->count())->toBe(0);
});

it('forbids adding points to a trip of another person of the tenant', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $trip = Trip::factory()->for($tenant)->for(User::factory()->forTenant($tenant))->create();

    $this->withToken(apiTokenFor($user, $tenant))
        ->postJson(route('api.v1.trips.points.store', $trip), ['points' => tripPoints(1)])
        ->assertForbidden();
});

it('validates the batch', function (Closure $makePoints, string $errorKey) {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $trip = Trip::factory()->for($tenant)->for($user)->create();

    $this->withToken(apiTokenFor($user, $tenant))
        ->postJson(route('api.v1.trips.points.store', $trip), ['points' => $makePoints()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errorKey);
})->with([
    'more than 500 points' => [fn () => tripPoints(501), 'points'],
    'latitude out of range' => [fn () => [[...tripPoints(1)[0], 'latitude' => 91]], 'points.0.latitude'],
    'unknown status' => [fn () => [[...tripPoints(1)[0], 'status' => 'X']], 'points.0.status'],
    'repeated id in the batch' => [fn () => array_fill(0, 2, tripPoints(1)[0]), 'points.0.id'],
]);

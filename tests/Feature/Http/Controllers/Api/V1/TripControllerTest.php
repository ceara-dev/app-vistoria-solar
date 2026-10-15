<?php

use App\Models\Tenant;
use App\Models\Trip;
use App\Models\User;
use App\Tenancy\TenantScope;
use Illuminate\Support\Str;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function tripPayload(array $overrides = []): array
{
    return [
        'started_at' => '2026-10-15T12:00:00Z',
        'finished_at' => '2026-10-15T13:30:00Z',
        'distance_m' => 15234.5,
        'moving_seconds' => 4800,
        'paused_seconds' => 600,
        'max_speed_ms' => 22.3,
        ...$overrides,
    ];
}

function findTrip(string $id): ?Trip
{
    return Trip::withoutGlobalScope(TenantScope::class)->find($id);
}

describe('update', function () {
    it('creates the trip with the device id, in the tenant and user of the token', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $tripId = (string) Str::uuid7();

        $this->withToken(apiTokenFor($user, $tenant))
            ->putJson(route('api.v1.trips.update', $tripId), tripPayload(['tenant_id' => 999, 'user_id' => 999]))
            ->assertCreated()
            ->assertJsonPath('data.id', $tripId)
            ->assertJsonPath('data.distance_m', 15234.5);

        $trip = findTrip($tripId);

        expect($trip->tenant_id)->toBe($tenant->id)
            ->and($trip->user_id)->toBe($user->id)
            ->and($trip->started_at->toIso8601String())->toBe('2026-10-15T12:00:00+00:00');
    });

    it('updates the same trip when it is sent again', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $trip = Trip::factory()->for($tenant)->for($user)->create(['finished_at' => null]);

        $this->withToken(apiTokenFor($user, $tenant))
            ->putJson(route('api.v1.trips.update', $trip->id), tripPayload([
                'started_at' => $trip->started_at->toIso8601String(),
                'finished_at' => $trip->started_at->addHour()->toIso8601String(),
            ]))
            ->assertOk();

        expect(findTrip($trip->id)->finished_at)->not->toBeNull()
            ->and(Trip::withoutGlobalScope(TenantScope::class)->count())->toBe(1);
    });

    it('refuses to change a trip of another person of the tenant', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $trip = Trip::factory()->for($tenant)->for(User::factory()->forTenant($tenant))->create(['distance_m' => 100]);

        $this->withToken(apiTokenFor($user, $tenant))
            ->putJson(route('api.v1.trips.update', $trip->id), tripPayload())
            ->assertForbidden();

        expect(findTrip($trip->id)->distance_m)->toBe(100.0);
    });

    it('answers 409 when the id already exists in another tenant', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $otherTenantTrip = Trip::factory()->create(['distance_m' => 100]);

        $this->withToken(apiTokenFor($user, $tenant))
            ->putJson(route('api.v1.trips.update', $otherTenantTrip->id), tripPayload())
            ->assertConflict();

        expect(findTrip($otherTenantTrip->id)->distance_m)->toBe(100.0);
    });

    it('rejects a trip that finishes before it starts', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();

        $this->withToken(apiTokenFor($user, $tenant))
            ->putJson(route('api.v1.trips.update', (string) Str::uuid7()), tripPayload(['finished_at' => '2026-10-15T11:00:00Z']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('finished_at');
    });

    it('answers 404 for an id that is not a UUID', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();

        $this->withToken(apiTokenFor($user, $tenant))
            ->putJson('/api/v1/trips/123', tripPayload())
            ->assertNotFound();
    });

    it('records the sync in the tenant history with the user of the token', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $tripId = (string) Str::uuid7();

        $this->withToken(apiTokenFor($user, $tenant))->putJson(route('api.v1.trips.update', $tripId), tripPayload());

        $audit = auditsOf($tenant, 'created')->firstWhere('auditable_id', $tripId);

        expect($audit->auditable_type)->toBe(Trip::class)
            ->and($audit->user_id)->toBe($user->id);
    });
});

describe('index', function () {
    it('lists only the trips of the user, newest first, with the point count', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $older = Trip::factory()->for($tenant)->for($user)->hasPoints(3)->create(['started_at' => now()->subDays(2)]);
        $newer = Trip::factory()->for($tenant)->for($user)->create(['started_at' => now()->subDay()]);
        Trip::factory()->for($tenant)->for(User::factory()->forTenant($tenant))->create();
        Trip::factory()->create();

        $this->withToken(apiTokenFor($user, $tenant))
            ->getJson(route('api.v1.trips.index'))
            ->assertOk()
            ->assertJsonPath('data.*.id', [$newer->id, $older->id])
            ->assertJsonPath('data.1.points_count', 3);
    });
});

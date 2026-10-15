<?php

namespace App\Repositories;

use App\Models\Trip;
use App\Models\TripPoint;
use App\Models\User;
use App\Repositories\Contracts\TripRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class TripRepository implements TripRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Trip>
     */
    public function paginateFor(User $user, int $perPage): LengthAwarePaginator
    {
        return Trip::query()
            ->whereBelongsTo($user)
            ->withCount('points')
            ->latest('started_at')
            ->paginate($perPage);
    }

    public function find(string $id): ?Trip
    {
        return Trip::find($id);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(string $id, User $user, array $attributes): Trip
    {
        $trip = new Trip($attributes);
        $trip->forceFill(['id' => $id, 'user_id' => $user->id])->save();

        return $trip;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Trip $trip, array $attributes): Trip
    {
        $trip->update($attributes);

        return $trip;
    }

    /**
     * Um INSERT por lote, por fora dos eventos de model: a empresa vem da
     * própria rota, já validada, e a chave estrangeira composta protege o resto.
     *
     * @param  list<array{id: string, latitude: float|string, longitude: float|string, recorded_at: string, status: string, speed_ms: float|string}>  $points
     */
    public function addPoints(Trip $trip, array $points): int
    {
        $rows = array_map(fn (array $point): array => [
            'id' => $point['id'],
            'tenant_id' => $trip->tenant_id,
            'trip_id' => $trip->id,
            'latitude' => $point['latitude'],
            'longitude' => $point['longitude'],
            'recorded_at' => Carbon::parse($point['recorded_at'])->utc()->format('Y-m-d H:i:s.v'),
            'status' => $point['status'],
            'speed_ms' => $point['speed_ms'],
        ], $points);

        return TripPoint::query()->insertOrIgnore($rows);
    }
}

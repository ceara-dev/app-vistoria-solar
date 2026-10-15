<?php

namespace App\Repositories\Contracts;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TripRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Trip>
     */
    public function paginateFor(User $user, int $perPage): LengthAwarePaginator;

    public function find(string $id): ?Trip;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(string $id, User $user, array $attributes): Trip;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Trip $trip, array $attributes): Trip;

    /**
     * Grava os pontos que ainda não existem e ignora os repetidos.
     *
     * @param  list<array{id: string, latitude: float|string, longitude: float|string, recorded_at: string, status: string, speed_ms: float|string}>  $points
     * @return int Quantos pontos eram novos
     */
    public function addPoints(Trip $trip, array $points): int;
}

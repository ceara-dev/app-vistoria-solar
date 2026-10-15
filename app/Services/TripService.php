<?php

namespace App\Services;

use App\Exceptions\SyncConflictException;
use App\Models\Trip;
use App\Models\User;
use App\Repositories\Contracts\TripRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Sincronização das rotas gravadas pelo app. Reenviar o mesmo dado não duplica nada.
 */
class TripService
{
    public const PER_PAGE = 20;

    public function __construct(
        protected TripRepositoryInterface $repository,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Trip>
     */
    public function listFor(User $user): LengthAwarePaginator
    {
        return $this->repository->paginateFor($user, self::PER_PAGE);
    }

    /**
     * Cria a rota com o id do aparelho ou atualiza a que já existe.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException a rota é de outra pessoa da empresa (403)
     * @throws SyncConflictException o id já existe fora da empresa (409)
     */
    public function sync(User $user, string $tripId, array $data): Trip
    {
        $trip = $this->repository->find($tripId);

        if ($trip !== null) {
            if ($trip->user_id !== $user->id) {
                throw new AuthorizationException('Esta rota foi enviada por outra pessoa.');
            }

            return $this->repository->update($trip, $data);
        }

        try {
            return $this->repository->create($tripId, $user, $data);
        } catch (UniqueConstraintViolationException) {
            // O escopo esconde a linha de outra empresa; só o banco percebe o choque.
            throw new SyncConflictException('Este identificador de rota já está em uso.');
        }
    }

    /**
     * @param  list<array{id: string, latitude: float|string, longitude: float|string, recorded_at: string, status: string, speed_ms: float|string}>  $points
     * @return int Quantos pontos eram novos
     */
    public function addPoints(Trip $trip, array $points): int
    {
        return $this->repository->addPoints($trip, $points);
    }
}

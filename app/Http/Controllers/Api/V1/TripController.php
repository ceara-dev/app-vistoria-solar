<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexTripRequest;
use App\Http\Requests\Api\V1\UpsertTripRequest;
use App\Http\Resources\Api\V1\TripResource;
use App\Services\TripService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TripController extends Controller
{
    public function __construct(
        protected TripService $service,
    ) {}

    /**
     * Rotas da própria pessoa, das mais recentes para as mais antigas.
     */
    public function index(IndexTripRequest $request): AnonymousResourceCollection
    {
        return TripResource::collection($this->service->listFor($request->user()));
    }

    /**
     * PUT idempotente: 201 quando a rota é nova, 200 quando já existia.
     */
    public function update(UpsertTripRequest $request, string $tripId): JsonResponse
    {
        $trip = $this->service->sync($request->user(), $tripId, $request->validated());

        return (new TripResource($trip))->response()->setStatusCode($trip->wasRecentlyCreated ? 201 : 200);
    }
}

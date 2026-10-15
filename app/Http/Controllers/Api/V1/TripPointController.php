<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTripPointsRequest;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Http\JsonResponse;

class TripPointController extends Controller
{
    public function __construct(
        protected TripService $service,
    ) {}

    /**
     * Lote de pontos. Pontos já recebidos são ignorados, então o app pode reenviar o lote.
     */
    public function store(StoreTripPointsRequest $request, Trip $trip): JsonResponse
    {
        $points = $request->validated('points');
        $stored = $this->service->addPoints($trip, $points);

        return response()->json(['data' => ['received' => count($points), 'stored' => $stored]]);
    }
}

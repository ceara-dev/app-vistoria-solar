<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Services\Central\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('Central/Dashboard/Index', [
            'stats' => $this->service->stats(),
        ]);
    }
}

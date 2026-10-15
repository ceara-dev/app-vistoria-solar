<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Plan\StorePlanRequest;
use App\Http\Requests\Central\Plan\UpdatePlanRequest;
use App\Http\Resources\Central\PlanResource;
use App\Models\Plan;
use App\Services\Central\PlanService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Planos comerciais. Sem exclusão: um plano sai de linha com `is_active`.
 */
class PlanController extends Controller
{
    public function __construct(
        protected PlanService $service,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Central/Plans/Index', [
            'plans' => PlanResource::collection($this->service->list()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Central/Plans/Create');
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $plan = $this->service->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => "Plano {$plan->name} criado."]);

        return to_route('central.plans.index');
    }

    public function edit(Plan $plan): Response
    {
        return Inertia::render('Central/Plans/Edit', [
            'plan' => new PlanResource($plan),
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $this->service->update($plan, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => "Plano {$plan->name} atualizado."]);

        return to_route('central.plans.index');
    }
}

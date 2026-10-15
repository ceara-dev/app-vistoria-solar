<?php

namespace App\Http\Controllers;

use App\Http\Requests\CurrentTenant\StoreTenantRequest;
use App\Http\Requests\CurrentTenant\SwitchTenantRequest;
use App\Http\Resources\TenantSummaryResource;
use App\Models\User;
use App\Services\CurrentTenantService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Escolha da empresa em que o usuário trabalha, troca e criação de empresa nova.
 */
class CurrentTenantController extends Controller
{
    public function __construct(
        protected CurrentTenantService $service,
    ) {}

    public function index(#[CurrentUser] User $user): Response
    {
        return Inertia::render('Auth/SelectTenant', [
            'tenants' => TenantSummaryResource::collection($this->service->availableTenants($user)),
        ]);
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $this->service->createTenant($request->user(), $request->validated('company_name'));

        return to_route('tenant.dashboard');
    }

    public function update(SwitchTenantRequest $request): RedirectResponse
    {
        $this->service->switchTo($request->user(), $request->integer('tenant_id'));

        return to_route('tenant.dashboard');
    }
}

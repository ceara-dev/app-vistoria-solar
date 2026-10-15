<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Tenant\IndexTenantRequest;
use App\Http\Requests\Central\Tenant\StoreTenantRequest;
use App\Http\Resources\Central\TenantResource;
use App\Models\Tenant;
use App\Services\Central\TenantService;
use App\Tenancy\TenantStatus;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Empresas da plataforma. O acesso é do middleware `central.admin`.
 */
class TenantController extends Controller
{
    public function __construct(
        protected TenantService $service,
    ) {}

    public function index(IndexTenantRequest $request): Response
    {
        $search = $request->validated('search');
        $status = $request->enum('status', TenantStatus::class);

        return Inertia::render('Central/Tenants/Index', [
            'tenants' => TenantResource::collection($this->service->paginate($search, $status)),
            'filters' => [
                'search' => $search,
                'status' => $status?->value,
            ],
            'statuses' => TenantStatus::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Central/Tenants/Create', [
            'plans' => $this->service->planOptions(),
            'b2bClients' => $this->service->b2bClientOptions(),
        ]);
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $tenant = $this->service->create($request->validated(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Empresa {$tenant->name} criada. Convite enviado para {$request->validated('admin_email')}.",
        ]);

        return to_route('central.tenants.index');
    }

    public function suspend(Tenant $tenant): RedirectResponse
    {
        $this->service->suspend($tenant);

        Inertia::flash('toast', ['type' => 'success', 'message' => "Empresa {$tenant->name} suspensa."]);

        return back();
    }

    public function activate(Tenant $tenant): RedirectResponse
    {
        $this->service->activate($tenant);

        Inertia::flash('toast', ['type' => 'success', 'message' => "Empresa {$tenant->name} reativada."]);

        return back();
    }

    public function extendTrial(Tenant $tenant): RedirectResponse
    {
        $this->service->extendTrial($tenant);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Teste de {$tenant->name} estendido até {$tenant->trial_ends_at->format('d/m/Y')}.",
        ]);

        return back();
    }
}

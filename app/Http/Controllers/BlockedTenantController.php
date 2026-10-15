<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CurrentTenantService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Aviso de que a empresa atual não permite acesso (suspensa, cancelada ou com teste vencido).
 */
class BlockedTenantController extends Controller
{
    public function __construct(
        protected CurrentTenantService $service,
    ) {}

    public function __invoke(#[CurrentUser] User $user): Response|RedirectResponse
    {
        $tenant = $this->service->currentTenantOf($user);

        if ($tenant === null || $tenant->allowsAccess()) {
            return to_route('tenant.dashboard');
        }

        return Inertia::render('Auth/TenantBlocked', [
            'tenantName' => $tenant->name,
            'reason' => $tenant->blockedReason(),
            'hasOtherTenants' => $this->service->availableTenants($user)->count() > 1,
        ]);
    }
}

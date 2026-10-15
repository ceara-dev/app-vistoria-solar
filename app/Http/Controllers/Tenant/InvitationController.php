<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Invitation\StoreInvitationRequest;
use App\Models\TenantInvitation;
use App\Services\InvitationService;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantRole;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationService $service,
        protected TenantContext $tenantContext,
    ) {}

    public function store(StoreInvitationRequest $request): RedirectResponse
    {
        $invitation = $this->service->invite(
            $this->tenantContext->require(),
            $request->user(),
            $request->validated('email'),
            $request->enum('role', TenantRole::class),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => "Convite enviado para {$invitation->email}."]);

        return back();
    }

    public function destroy(TenantInvitation $invitation): RedirectResponse
    {
        $this->service->revoke($invitation);

        Inertia::flash('toast', ['type' => 'success', 'message' => "Convite para {$invitation->email} cancelado."]);

        return back();
    }
}

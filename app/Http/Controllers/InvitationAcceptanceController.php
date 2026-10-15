<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invitation\AcceptInvitationRequest;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Services\InvitationService;
use App\Tenancy\InvitationAcceptanceMode;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Link recebido por e-mail. Funciona para quem já tem conta e para quem ainda não tem.
 */
class InvitationAcceptanceController extends Controller
{
    public function __construct(
        protected InvitationService $service,
    ) {}

    public function show(string $token, #[CurrentUser] ?User $user): Response
    {
        $invitation = $this->findInvitation($token);
        $mode = $this->service->acceptanceModeFor($invitation, $user);

        if ($mode === InvitationAcceptanceMode::Login) {
            redirect()->setIntendedUrl(route('invitations.show', $token));
        }

        return Inertia::render('Auth/AcceptInvitation', [
            'token' => $token,
            'mode' => $mode->value,
            'invitation' => [
                'email' => $invitation->email,
                'tenant_name' => $invitation->tenant->name,
                'role_label' => $invitation->role->label(),
            ],
        ]);
    }

    public function store(AcceptInvitationRequest $request, string $token): RedirectResponse
    {
        $invitation = $this->findInvitation($token);
        $user = $request->user();

        if ($user === null) {
            Auth::login($this->service->acceptWithNewAccount($invitation, $request->validated()));
            $request->session()->regenerate();
        } else {
            $this->service->accept($invitation, $user);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => "Bem-vindo à empresa {$invitation->tenant->name}!"]);

        return to_route('tenant.dashboard');
    }

    private function findInvitation(string $token): TenantInvitation
    {
        return $this->service->findPendingByToken($token) ?? abort(404);
    }
}

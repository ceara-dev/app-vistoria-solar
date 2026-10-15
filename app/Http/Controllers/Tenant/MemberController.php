<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Member\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Http\Resources\TenantInvitationResource;
use App\Models\User;
use App\Services\MemberService;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantRole;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Membros da empresa atual. `{member}` só resolve membros dela (ver AppServiceProvider).
 */
class MemberController extends Controller
{
    public function __construct(
        protected MemberService $service,
        protected TenantContext $tenantContext,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Tenant/Members/Index', [
            'members' => MemberResource::collection($this->service->list($this->tenantContext->require())),
            'invitations' => TenantInvitationResource::collection($this->service->pendingInvitations()),
            'roles' => TenantRole::options(),
        ]);
    }

    public function update(UpdateMemberRequest $request, User $member): RedirectResponse
    {
        $this->service->changeRole(
            $this->tenantContext->require(),
            $request->user(),
            $member,
            $request->enum('role', TenantRole::class),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => "Papel de {$member->name} atualizado."]);

        return back();
    }

    public function destroy(#[CurrentUser] User $user, User $member): RedirectResponse
    {
        $this->service->deactivate($this->tenantContext->require(), $user, $member);

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$member->name} não tem mais acesso à empresa."]);

        return back();
    }
}

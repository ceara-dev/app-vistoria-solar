<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiSessionResource;
use App\Models\User;
use App\Services\AuthTokenService;
use App\Tenancy\TenantContext;
use Illuminate\Container\Attributes\CurrentUser;

/**
 * Confere o token ao abrir o app e devolve a sessão atual.
 */
class MeController extends Controller
{
    public function __construct(
        protected AuthTokenService $service,
        protected TenantContext $tenantContext,
    ) {}

    public function __invoke(#[CurrentUser] User $user): ApiSessionResource
    {
        return new ApiSessionResource($this->service->currentSession($user, $this->tenantContext->require()));
    }
}

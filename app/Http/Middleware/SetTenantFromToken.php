<?php

namespace App\Http\Middleware;

use App\Exceptions\TenantUnavailableException;
use App\Models\PersonalAccessToken;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Define a empresa da requisição da API a partir do token (`personal_access_tokens.tenant_id`).
 *
 * Equivale ao SetCurrentTenant da web e também roda antes do SubstituteBindings.
 */
class SetTenantFromToken
{
    public function __construct(private TenantContext $tenantContext) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws AuthenticationException
     * @throws TenantUnavailableException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? throw new AuthenticationException;
        $token = $user->currentAccessToken();
        $tenant = $token instanceof PersonalAccessToken ? $token->tenant : null;

        // Sem empresa, ou a pessoa saiu dela: o token não serve mais.
        if ($tenant === null || ! $user->isActiveMemberOf($tenant)) {
            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }

            throw new AuthenticationException('Sua sessão nesta empresa foi encerrada.');
        }

        if (! $tenant->allowsAccess()) {
            throw new TenantUnavailableException($tenant);
        }

        $this->tenantContext->set($tenant);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $next($request);
    }
}

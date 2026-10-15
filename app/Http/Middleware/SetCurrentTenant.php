<?php

namespace App\Http\Middleware;

use App\Tenancy\CurrentTenantResolver;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Define a empresa atual da requisição a partir do usuário logado.
 *
 * Precisa rodar antes do SubstituteBindings (ver bootstrap/app.php) para que
 * os models da rota já sejam buscados dentro da empresa.
 */
class SetCurrentTenant
{
    public function __construct(
        private CurrentTenantResolver $currentTenantResolver,
        private TenantContext $tenantContext,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? throw new AuthenticationException;
        $tenant = $this->currentTenantResolver->resolve($user);

        if ($tenant === null) {
            return to_route('tenants.select');
        }

        if (! $tenant->allowsAccess()) {
            return to_route('tenants.blocked');
        }

        $this->tenantContext->set($tenant);

        // O spatie guarda os papéis já carregados; eles precisam ser os desta empresa.
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $next($request);
    }
}

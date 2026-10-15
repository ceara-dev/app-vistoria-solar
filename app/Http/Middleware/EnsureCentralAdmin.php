<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Só a equipe do SaaS entra na Central. A decisão é o `is_central_admin`,
 * nunca uma permissão de empresa.
 */
class EnsureCentralAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_central_admin !== true) {
            abort(403);
        }

        return $next($request);
    }
}

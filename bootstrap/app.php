<?php

use App\Http\Middleware\EnsureCentralAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetCurrentTenant;
use App\Http\Middleware\SetTenantFromToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'tenant' => SetCurrentTenant::class,
            'tenant.token' => SetTenantFromToken::class,
            'central.admin' => EnsureCentralAdmin::class,
        ]);

        // A empresa precisa estar definida antes do route model binding.
        $middleware->prependToPriorityList(SubstituteBindings::class, SetCurrentTenant::class);
        $middleware->prependToPriorityList(SubstituteBindings::class, SetTenantFromToken::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

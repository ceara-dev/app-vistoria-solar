<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(
        protected TenantContext $tenantContext,
        protected TenantRepositoryInterface $tenants,
    ) {}

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * Este método roda antes do middleware `tenant`. Tudo o que depende da
     * empresa é closure, resolvida só na hora de montar a resposta.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn (): ?array => $request->user()?->only('id', 'name', 'email'),
            ],
            'tenant' => fn (): ?array => $this->tenantContext->get()?->only('id', 'name'),
            'tenants' => fn (): array => $request->user() === null
                ? []
                : $this->tenants->activeTenantsOf($request->user())
                    ->map(fn (Tenant $tenant): array => $tenant->only('id', 'name'))
                    ->all(),
            'can' => fn (): array => $request->user() === null || $this->tenantContext->get() === null
                ? []
                : $request->user()->getAllPermissions()->pluck('name')->all(),
        ];
    }
}

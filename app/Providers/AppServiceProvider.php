<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Central\B2bClientRepository as CentralB2bClientRepository;
use App\Repositories\Central\Contracts\B2bClientRepositoryInterface as CentralB2bClientRepositoryInterface;
use App\Repositories\Central\Contracts\PlanRepositoryInterface as CentralPlanRepositoryInterface;
use App\Repositories\Central\Contracts\TenantRepositoryInterface as CentralTenantRepositoryInterface;
use App\Repositories\Central\PlanRepository as CentralPlanRepository;
use App\Repositories\Central\TenantRepository as CentralTenantRepository;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\MemberRepositoryInterface;
use App\Repositories\Contracts\TenantInvitationRepositoryInterface;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Repositories\Contracts\TripRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\MemberRepository;
use App\Repositories\TenantInvitationRepository;
use App\Repositories\TenantRepository;
use App\Repositories\TripRepository;
use App\Repositories\UserRepository;
use App\Tenancy\TenantContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Log\Context\Repository;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);

        $this->app->bind(TenantRepositoryInterface::class, TenantRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(MemberRepositoryInterface::class, MemberRepository::class);
        $this->app->bind(TenantInvitationRepositoryInterface::class, TenantInvitationRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(TripRepositoryInterface::class, TripRepository::class);

        $this->app->bind(CentralTenantRepositoryInterface::class, CentralTenantRepository::class);
        $this->app->bind(CentralPlanRepositoryInterface::class, CentralPlanRepository::class);
        $this->app->bind(CentralB2bClientRepositoryInterface::class, CentralB2bClientRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->restoreTenantInQueuedJobs();
        $this->bindTenantMembers();
        $this->configureApi();
    }

    /**
     * Tokens do app com empresa, e limites da API: por token nas rotas
     * autenticadas e por e-mail e IP no login.
     */
    private function configureApi(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(120)
            ->by((string) ($request->user()?->currentAccessToken()?->getKey() ?? $request->ip())));

        RateLimiter::for('api-login', fn (Request $request): Limit => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('email')).'|'.$request->ip()));
    }

    /**
     * O Laravel leva o Context para o payload de cada job e o hidrata no
     * worker, mesmo quando vazio. A empresa do job sai dele, e um job sem
     * empresa zera a do job anterior.
     */
    private function restoreTenantInQueuedJobs(): void
    {
        Context::hydrated(function (Repository $context): void {
            $tenantId = $context->get('tenant_id');

            $this->app->make(TenantContext::class)->set(
                $tenantId === null ? null : Tenant::find($tenantId),
            );
        });
    }

    /**
     * `User` não tem escopo de empresa, então `{member}` só resolve quem tem
     * vínculo com a empresa atual. Os demais dão 404.
     */
    private function bindTenantMembers(): void
    {
        Route::bind('member', fn (string $value): User => $this->app->make(TenantContext::class)
            ->require()
            ->users()
            ->findOrFail($value));
    }
}

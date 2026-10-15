<?php

namespace App\Services;

use App\Exceptions\TenantSelectionRequiredException;
use App\Exceptions\TenantUnavailableException;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\MemberRepositoryInterface;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Login do app: cada token vale para uma única empresa (doc 05, seção 2).
 */
class AuthTokenService
{
    public const TOKEN_LIFETIME_DAYS = 30;

    public function __construct(
        protected UserRepositoryInterface $users,
        protected TenantRepositoryInterface $tenants,
        protected MemberRepositoryInterface $members,
    ) {}

    /**
     * @throws ValidationException credenciais inválidas (422)
     * @throws AuthorizationException sem vínculo com a empresa pedida ou com nenhuma (403)
     * @throws TenantSelectionRequiredException várias empresas e nenhuma escolhida (409)
     * @throws TenantUnavailableException empresa sem acesso (423)
     */
    public function issueToken(string $email, string $password, string $deviceName, ?int $tenantId): ApiSession
    {
        $user = $this->users->findByEmail($email);

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        $tenant = $this->resolveTenant($user, $tenantId);

        if (! $tenant->allowsAccess()) {
            throw new TenantUnavailableException($tenant);
        }

        $expiresAt = now()->addDays(self::TOKEN_LIFETIME_DAYS);
        $newToken = $this->users->issueToken($user, $tenant, $deviceName, $expiresAt);

        return new ApiSession($user, $tenant, $this->members->permissionsOf($tenant, $user), $newToken->plainTextToken, $expiresAt);
    }

    public function revokeCurrentToken(User $user): void
    {
        $this->users->revokeCurrentToken($user);
    }

    /**
     * Sessão do token em uso. A empresa vem do middleware `tenant.token`.
     */
    public function currentSession(User $user, Tenant $tenant): ApiSession
    {
        return new ApiSession($user, $tenant, $this->members->permissionsOf($tenant, $user));
    }

    /**
     * @throws AuthorizationException
     * @throws TenantSelectionRequiredException
     */
    private function resolveTenant(User $user, ?int $tenantId): Tenant
    {
        $tenants = $this->tenants->activeTenantsOf($user);

        if ($tenantId !== null) {
            return $tenants->firstWhere('id', $tenantId)
                ?? throw new AuthorizationException('Você não tem acesso a esta empresa.');
        }

        if ($tenants->isEmpty()) {
            throw new AuthorizationException('Você não participa de nenhuma empresa ativa.');
        }

        if ($tenants->count() > 1) {
            throw new TenantSelectionRequiredException($tenants);
        }

        return $tenants->first();
    }
}

<?php

use App\Models\Audit;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Contracts\MemberRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Os testes de Feature usam a aplicação Laravel e um banco SQLite em
| memória recriado a cada teste.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Papel do usuário na empresa, lido como a aplicação lê.
 */
function tenantRoleOf(Tenant $tenant, User $user): ?TenantRole
{
    return app(MemberRepositoryInterface::class)->roleOf($tenant, $user);
}

/**
 * Coloca o usuário trabalhando na empresa, como o middleware `tenant` faz.
 */
function actAsMemberIn(User $user, Tenant $tenant): void
{
    app(TenantContext::class)->set($tenant);
    $user->unsetRelation('roles')->unsetRelation('permissions');
}

/**
 * Vincula um usuário existente a mais uma empresa, com papel.
 */
function addMembership(Tenant $tenant, User $user, TenantRole $role): void
{
    $members = app(MemberRepositoryInterface::class);
    $members->activate($tenant, $user);
    $members->assignRole($tenant, $user, $role);
}

/**
 * Histórico da empresa, do mais recente para o mais antigo.
 *
 * @return Collection<int, Audit>
 */
function auditsOf(Tenant $tenant, ?string $event = null): Collection
{
    return app(TenantContext::class)->run($tenant, fn (): Collection => Audit::query()
        ->when($event !== null, fn ($query) => $query->where('event', $event))
        ->latest('id')
        ->get());
}

/**
 * Token do app para o usuário na empresa, como o login da API emite.
 */
function apiTokenFor(User $user, Tenant $tenant): string
{
    return app(UserRepositoryInterface::class)->issueToken($user, $tenant, 'Teste', now()->addDays(30))->plainTextToken;
}

/**
 * Usuário da equipe da Central, autenticado.
 */
function actingAsCentralAdmin(): User
{
    $admin = User::factory()->centralAdmin()->create();
    test()->actingAs($admin);

    return $admin;
}

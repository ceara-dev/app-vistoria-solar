<?php

namespace App\Services\Central;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Central\Contracts\PlanRepositoryInterface;
use App\Repositories\Central\Contracts\TenantRepositoryInterface;
use App\Services\InvitationService;
use App\Services\RegistrationService;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Gestão das empresas pela equipe da Central.
 */
class TenantService
{
    public const TRIAL_EXTENSION_DAYS = 14;

    public function __construct(
        protected TenantRepositoryInterface $tenants,
        protected PlanRepositoryInterface $plans,
        protected B2bClientService $b2bClients,
        protected InvitationService $invitations,
        protected TenantContext $tenantContext,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Tenant>
     */
    public function paginate(?string $search, ?TenantStatus $status): LengthAwarePaginator
    {
        $paginator = $this->tenants->paginate($search, $status, 15);

        $customersCount = $this->tenants->customersCountByTenant($paginator->getCollection()->modelKeys());

        return $paginator->through(function (Tenant $tenant) use ($customersCount): Tenant {
            $tenant->setAttribute('customers_count', $customersCount[$tenant->id] ?? 0);

            return $tenant;
        });
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public function planOptions(): array
    {
        return $this->plans->active()
            ->map(fn (Plan $plan): array => ['value' => $plan->id, 'label' => $plan->name])
            ->all();
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public function b2bClientOptions(): array
    {
        return $this->b2bClients->options();
    }

    /**
     * Cria a empresa em período de teste e convida o administrador como
     * proprietário. O convite roda no contexto da empresa nova, porque a
     * linha de `tenant_invitations` é dela.
     *
     * @param  array{name: string, admin_email: string, plan_id?: int|null, b2b_client_id?: int|null}  $data
     */
    public function create(array $data, User $inviter): Tenant
    {
        return DB::transaction(function () use ($data, $inviter): Tenant {
            $tenant = $this->tenants->createTrial(
                $data['name'],
                now()->addDays(RegistrationService::TRIAL_DAYS),
                $data['plan_id'] ?? null,
                $data['b2b_client_id'] ?? null,
            );

            $this->tenantContext->run(
                $tenant,
                fn () => $this->invitations->inviteOwner($tenant, $inviter, $data['admin_email']),
            );

            return $tenant;
        });
    }

    /**
     * @throws ValidationException
     */
    public function suspend(Tenant $tenant): void
    {
        $this->ensureStatus($tenant, [TenantStatus::Trial, TenantStatus::Active], 'Só é possível suspender uma empresa em teste ou ativa.');

        $this->tenants->updateStatus($tenant, TenantStatus::Suspended);
    }

    /**
     * @throws ValidationException
     */
    public function activate(Tenant $tenant): void
    {
        $this->ensureStatus($tenant, [TenantStatus::Suspended], 'Só é possível reativar uma empresa suspensa.');

        $this->tenants->updateStatus($tenant, TenantStatus::Active);
    }

    /**
     * Estende o teste por mais duas semanas, contando do fim atual (ou de
     * agora, quando o prazo já venceu). Atualiza o model para o aviso da tela.
     *
     * @throws ValidationException
     */
    public function extendTrial(Tenant $tenant): void
    {
        $this->ensureStatus($tenant, [TenantStatus::Trial], 'Só é possível estender o teste de uma empresa em teste.');

        $base = $tenant->trial_ends_at?->isFuture() === true ? $tenant->trial_ends_at : now();

        $this->tenants->extendTrial($tenant, $base->addDays(self::TRIAL_EXTENSION_DAYS));

        $tenant->refresh();
    }

    /**
     * @param  list<TenantStatus>  $allowed
     *
     * @throws ValidationException
     */
    private function ensureStatus(Tenant $tenant, array $allowed, string $message): void
    {
        if (! in_array($tenant->status, $allowed, true)) {
            throw ValidationException::withMessages(['tenant' => $message]);
        }
    }
}

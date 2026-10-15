<?php

use App\Models\B2bClient;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use App\Services\RegistrationService;
use App\Tenancy\TenantRole;
use App\Tenancy\TenantScope;
use App\Tenancy\TenantStatus;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function centralTenantPayload(array $overrides = []): array
{
    return array_replace([
        'name' => 'Empresa Nova',
        'admin_email' => 'gestor@example.com',
        'plan_id' => null,
    ], $overrides);
}

/**
 * O convite é de empresa, então a leitura sai do escopo, como a Central faz.
 */
function latestInvitation(): TenantInvitation
{
    return TenantInvitation::withoutGlobalScope(TenantScope::class)->latest('id')->sole();
}

describe('index', function () {
    it('lists the companies with plan, b2b client, active members and customers', function () {
        actingAsCentralAdmin();

        $tenant = Tenant::factory()
            ->onPlan(Plan::factory()->create(['name' => 'Padrão']))
            ->for(B2bClient::factory()->create(['legal_name' => 'Cliente LTDA', 'trade_name' => 'Cliente']), 'b2bClient')
            ->create(['name' => 'Alpha Solar']);
        User::factory()->forTenant($tenant, TenantRole::Owner)->create();
        User::factory()->forTenant($tenant, TenantRole::Manager)->create();
        Customer::factory()->for($tenant)->count(3)->create();

        $removed = User::factory()->create();
        $tenant->users()->attach($removed->id, ['is_active' => false]);

        $otherTenant = Tenant::factory()->create(['name' => 'Zulu Solar']);
        Customer::factory()->for($otherTenant)->count(5)->create();

        $this->get(route('central.tenants.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Central/Tenants/Index')
                ->has('tenants.data', 2)
                ->where('tenants.data.0.name', 'Alpha Solar')
                ->where('tenants.data.0.plan.name', 'Padrão')
                ->where('tenants.data.0.b2b_client.name', 'Cliente')
                ->where('tenants.data.0.status', 'active')
                ->where('tenants.data.0.status_label', 'Ativa')
                ->where('tenants.data.0.members_count', 2)
                ->where('tenants.data.0.customers_count', 3)
                ->where('tenants.data.1.customers_count', 5)
                ->has('statuses', 4));
    });

    it('filters by status', function () {
        actingAsCentralAdmin();

        Tenant::factory()->create(['name' => 'Ativa']);
        Tenant::factory()->suspended()->create(['name' => 'Suspensa']);

        $this->get(route('central.tenants.index', ['status' => 'suspended']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('tenants.data', 1)
                ->where('tenants.data.0.name', 'Suspensa')
                ->where('filters.status', 'suspended'));
    });

    it('searches by name or by the digits of the document', function (string $search) {
        actingAsCentralAdmin();

        Tenant::factory()->create(['name' => 'Solaris', 'document' => '12345678000199']);
        Tenant::factory()->create(['name' => 'Outra Empresa', 'document' => '99887766000155']);

        $this->get(route('central.tenants.index', ['search' => $search]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('tenants.data', 1)
                ->where('tenants.data.0.name', 'Solaris'));
    })->with(['by name' => 'solar', 'by formatted document' => '12.345.678']);

    it('paginates 15 companies per page', function () {
        actingAsCentralAdmin();

        Tenant::factory()->count(16)->create();

        $this->get(route('central.tenants.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('tenants.data', 15)
                ->where('tenants.meta.total', 16)
                ->where('tenants.meta.last_page', 2));
    });
});

describe('create', function () {
    it('offers the active plans and the b2b clients', function () {
        actingAsCentralAdmin();

        $active = Plan::factory()->create(['name' => 'Padrão', 'is_active' => true]);
        Plan::factory()->inactive()->create(['name' => 'Descontinuado']);
        B2bClient::factory()->create(['legal_name' => 'Cliente LTDA', 'trade_name' => 'Cliente']);

        $this->get(route('central.tenants.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Central/Tenants/Create')
                ->has('plans', 1)
                ->where('plans.0.value', $active->id)
                ->where('plans.0.label', 'Padrão')
                ->has('b2bClients', 1)
                ->where('b2bClients.0.label', 'Cliente'));
    });
});

describe('store', function () {
    it('creates a trial company and invites the administrator as owner', function () {
        Notification::fake();

        $admin = actingAsCentralAdmin();
        $plan = Plan::factory()->create();
        $b2bClient = B2bClient::factory()->create();

        $response = $this->post(route('central.tenants.store'), centralTenantPayload([
            'name' => 'Nova Empresa',
            'admin_email' => 'Gestor@Example.COM',
            'plan_id' => $plan->id,
            'b2b_client_id' => $b2bClient->id,
        ]));

        $tenant = Tenant::sole();

        $response->assertRedirect(route('central.tenants.index'))
            ->assertInertiaFlash('toast.type', 'success');

        expect($tenant->status)->toBe(TenantStatus::Trial)
            ->and($tenant->trial_ends_at->isSameDay(now()->addDays(RegistrationService::TRIAL_DAYS)))->toBeTrue()
            ->and($tenant->plan_id)->toBe($plan->id)
            ->and($tenant->b2b_client_id)->toBe($b2bClient->id)
            ->and($tenant->users()->count())->toBe(0);

        $invitation = latestInvitation();

        expect($invitation->tenant_id)->toBe($tenant->id)
            ->and($invitation->email)->toBe('gestor@example.com')
            ->and($invitation->role)->toBe(TenantRole::Owner)
            ->and($invitation->invited_by)->toBe($admin->id);

        Notification::assertSentOnDemand(
            TenantInvitationNotification::class,
            fn (TenantInvitationNotification $notification, array $channels, object $notifiable): bool => $notifiable->routes['mail'] === 'gestor@example.com',
        );

        $creationAudit = auditsOf($tenant, 'created')
            ->where('auditable_type', Tenant::class)
            ->where('user_id', $admin->id);

        expect($creationAudit)->toHaveCount(1);
    });

    it('accepts a company without a plan or a b2b client', function () {
        Notification::fake();
        actingAsCentralAdmin();

        $this->post(route('central.tenants.store'), centralTenantPayload())
            ->assertSessionHasNoErrors();

        expect(Tenant::sole()->plan_id)->toBeNull()
            ->and(Tenant::sole()->b2b_client_id)->toBeNull();
    });

    it('validates the name, the administrator email, the plan and the b2b client', function () {
        Notification::fake();
        actingAsCentralAdmin();

        Plan::factory()->inactive()->create();

        $this->post(route('central.tenants.store'), centralTenantPayload([
            'name' => null,
            'admin_email' => 'não é e-mail',
            'plan_id' => Plan::sole()->id,
            'b2b_client_id' => 9999,
        ]))->assertSessionHasErrors(['name', 'admin_email', 'plan_id', 'b2b_client_id']);

        expect(Tenant::count())->toBe(0);
    });
});

describe('suspend', function () {
    it('suspends a company in trial or active', function (callable $state) {
        actingAsCentralAdmin();

        $tenant = $state()->create(['name' => 'Empresa']);

        $response = $this->put(route('central.tenants.suspend', $tenant));

        $response->assertRedirect()->assertInertiaFlash('toast.type', 'success');

        expect($tenant->refresh()->status)->toBe(TenantStatus::Suspended);
    })->with([
        'in trial' => [fn () => Tenant::factory()->trial()],
        'active' => [fn () => Tenant::factory()],
    ]);

    it('does not suspend a company that is already out', function (callable $state, string $message) {
        actingAsCentralAdmin();

        $tenant = $state()->create();
        $before = $tenant->status;

        $this->put(route('central.tenants.suspend', $tenant))
            ->assertSessionHasErrors(['tenant' => $message]);

        expect($tenant->refresh()->status)->toBe($before);
    })->with([
        'suspended' => [fn () => Tenant::factory()->suspended(), 'Só é possível suspender uma empresa em teste ou ativa.'],
        'canceled' => [fn () => Tenant::factory()->canceled(), 'Só é possível suspender uma empresa em teste ou ativa.'],
    ]);
});

describe('activate', function () {
    it('reactivates a suspended company', function () {
        actingAsCentralAdmin();

        $tenant = Tenant::factory()->suspended()->create(['name' => 'Empresa']);

        $this->put(route('central.tenants.activate', $tenant))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'success');

        expect($tenant->refresh()->status)->toBe(TenantStatus::Active);
    });

    it('does not reactivate a company that is not suspended', function () {
        actingAsCentralAdmin();

        $tenant = Tenant::factory()->create();

        $this->put(route('central.tenants.activate', $tenant))
            ->assertSessionHasErrors(['tenant' => 'Só é possível reativar uma empresa suspensa.']);

        expect($tenant->refresh()->status)->toBe(TenantStatus::Active);
    });
});

describe('extend trial', function () {
    it('extends the trial counting from the current end', function () {
        actingAsCentralAdmin();

        $endsAt = now()->addDays(3);
        $tenant = Tenant::factory()->trial()->create(['trial_ends_at' => $endsAt]);

        $response = $this->put(route('central.tenants.extend-trial', $tenant));

        $response->assertRedirect()->assertInertiaFlash('toast.type', 'success');

        expect($tenant->refresh()->trial_ends_at->isSameDay($endsAt->copy()->addDays(14)))->toBeTrue();
    });

    it('extends the trial from now when it is already over', function () {
        actingAsCentralAdmin();

        $tenant = Tenant::factory()->expiredTrial()->create();

        $this->put(route('central.tenants.extend-trial', $tenant))->assertSessionHasNoErrors();

        expect($tenant->refresh()->trial_ends_at->isSameDay(now()->addDays(14)))->toBeTrue()
            ->and($tenant->status)->toBe(TenantStatus::Trial);
    });

    it('does not extend the trial of a company that is not in trial', function () {
        actingAsCentralAdmin();

        $tenant = Tenant::factory()->create();

        $this->put(route('central.tenants.extend-trial', $tenant))
            ->assertSessionHasErrors(['tenant' => 'Só é possível estender o teste de uma empresa em teste.']);
    });
});

<?php

use App\Models\User;
use App\Tenancy\TenantRole;
use App\Tenancy\TenantStatus;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'company_name' => 'Solar Teste',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('tenant.dashboard', absolute: false));
});

test('registration creates a trial company owned by the new user', function () {
    $this->post('/register', [
        'name' => 'Ana Souza',
        'email' => 'ana@example.com',
        'company_name' => 'Solar Ana',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'ana@example.com')->sole();
    $tenant = $user->currentTenant;

    expect($tenant->name)->toBe('Solar Ana')
        ->and($tenant->status)->toBe(TenantStatus::Trial)
        ->and($tenant->trial_ends_at->isFuture())->toBeTrue()
        ->and($user->isActiveMemberOf($tenant))->toBeTrue()
        ->and(tenantRoleOf($tenant, $user))->toBe(TenantRole::Owner);
});

test('registration stores the email in lowercase', function () {
    $this->post('/register', [
        'name' => 'Ana Souza',
        'email' => 'Ana@Example.com',
        'company_name' => 'Solar Ana',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(User::where('email', 'ana@example.com')->exists())->toBeTrue();
});

test('registration requires the company name', function () {
    $response = $this->post('/register', [
        'name' => 'Ana Souza',
        'email' => 'ana@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['company_name' => 'Este campo é obrigatório']);
    $this->assertGuest();
});

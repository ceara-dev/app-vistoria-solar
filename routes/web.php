<?php

use App\Http\Controllers\BlockedTenantController;
use App\Http\Controllers\Central\B2bClientController as CentralB2bClientController;
use App\Http\Controllers\Central\DashboardController as CentralDashboardController;
use App\Http\Controllers\Central\PlanController as CentralPlanController;
use App\Http\Controllers\Central\TenantController as CentralTenantController;
use App\Http\Controllers\CurrentTenantController;
use App\Http\Controllers\InvitationAcceptanceController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\InvitationController;
use App\Http\Controllers\Tenant\MemberController;
use App\Models\Customer;
use App\Tenancy\TenantPermission;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Fora do middleware `tenant`: é aqui que a pessoa chega quando ainda não tem empresa definida.
    Route::get('/tenants', [CurrentTenantController::class, 'index'])->name('tenants.select');
    Route::post('/tenants', [CurrentTenantController::class, 'store'])->name('tenants.store');
    Route::put('/current-tenant', [CurrentTenantController::class, 'update'])->name('current-tenant.update');
    Route::get('/tenant-blocked', BlockedTenantController::class)->name('tenants.blocked');

    Route::middleware('tenant')->prefix('app')->name('tenant.')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::resource('customers', CustomerController::class)
            ->middlewareFor('create', 'can:create,'.Customer::class)
            ->middlewareFor('show', 'can:view,customer')
            ->middlewareFor('edit', 'can:update,customer')
            ->middlewareFor('destroy', 'can:delete,customer');

        Route::get('/members', [MemberController::class, 'index'])
            ->can(TenantPermission::MembersView->value)
            ->name('members.index');
        Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
        Route::delete('/members/{member}', [MemberController::class, 'destroy'])
            ->can(TenantPermission::MembersManage->value)
            ->name('members.destroy');

        Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
        Route::delete('/invitations/{invitation}', [InvitationController::class, 'destroy'])
            ->can('delete', 'invitation')
            ->name('invitations.destroy');
    });
});

Route::middleware(['auth', 'verified', 'central.admin'])->prefix('central')->name('central.')->group(function () {
    Route::get('/dashboard', CentralDashboardController::class)->name('dashboard');

    Route::get('/tenants', [CentralTenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/create', [CentralTenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [CentralTenantController::class, 'store'])->name('tenants.store');
    Route::put('/tenants/{tenant}/suspend', [CentralTenantController::class, 'suspend'])->name('tenants.suspend');
    Route::put('/tenants/{tenant}/activate', [CentralTenantController::class, 'activate'])->name('tenants.activate');
    Route::put('/tenants/{tenant}/extend-trial', [CentralTenantController::class, 'extendTrial'])->name('tenants.extend-trial');

    Route::get('/b2b-clients', [CentralB2bClientController::class, 'index'])->name('b2b-clients.index');
    Route::get('/b2b-clients/create', [CentralB2bClientController::class, 'create'])->name('b2b-clients.create');
    Route::post('/b2b-clients', [CentralB2bClientController::class, 'store'])->name('b2b-clients.store');
    Route::get('/b2b-clients/{b2bClient}/edit', [CentralB2bClientController::class, 'edit'])->name('b2b-clients.edit');
    Route::put('/b2b-clients/{b2bClient}', [CentralB2bClientController::class, 'update'])->name('b2b-clients.update');
    Route::delete('/b2b-clients/{b2bClient}', [CentralB2bClientController::class, 'destroy'])->name('b2b-clients.destroy');

    Route::get('/plans', [CentralPlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/create', [CentralPlanController::class, 'create'])->name('plans.create');
    Route::post('/plans', [CentralPlanController::class, 'store'])->name('plans.store');
    Route::get('/plans/{plan}/edit', [CentralPlanController::class, 'edit'])->name('plans.edit');
    Route::put('/plans/{plan}', [CentralPlanController::class, 'update'])->name('plans.update');
});

Route::middleware('throttle:20,1')->group(function () {
    Route::get('/invitations/{token}', [InvitationAcceptanceController::class, 'show'])->name('invitations.show');
    Route::post('/invitations/{token}', [InvitationAcceptanceController::class, 'store'])->name('invitations.accept');
});

require __DIR__.'/auth.php';

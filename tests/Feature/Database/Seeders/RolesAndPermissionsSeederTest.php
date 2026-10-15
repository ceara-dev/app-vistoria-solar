<?php

use App\Tenancy\TenantPermission;
use App\Tenancy\TenantRole;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    // O TestCase já semeia os papéis. Aqui o seeder precisa partir do zero.
    Role::query()->delete();
    Permission::query()->delete();
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('creates every role as a global role with its permissions', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    foreach (TenantRole::cases() as $tenantRole) {
        $role = Role::where('name', $tenantRole->value)->whereNull('tenant_id')->sole();

        expect($role->permissions->pluck('name')->sort()->values()->all())->toBe(
            collect($tenantRole->permissions())->map(fn (TenantPermission $permission) => $permission->value)->sort()->values()->all(),
        );
    }
});

it('seeds with model events disabled, as the DatabaseSeeder runs it', function () {
    Model::withoutEvents(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    expect(Role::findByName(TenantRole::Admin->value)->hasPermissionTo(TenantPermission::MembersManage->value))->toBeTrue();
});

it('can run again without duplicating roles or permissions', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Role::count())->toBe(count(TenantRole::cases()))
        ->and(Permission::count())->toBe(count(TenantPermission::cases()));
});

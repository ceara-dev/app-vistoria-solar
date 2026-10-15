<?php

namespace Database\Seeders;

use App\Tenancy\TenantPermission;
use App\Tenancy\TenantRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Cria os papéis globais e suas permissões. Pode rodar mais de uma vez e
 * deve rodar a cada deploy que mudar TenantRole ou TenantPermission.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Papéis globais têm tenant_id nulo.
        setPermissionsTeamId(null);

        foreach (TenantPermission::cases() as $permission) {
            Permission::findOrCreate($permission->value);
        }

        // Quem chama com WithoutModelEvents (o DatabaseSeeder) desliga a limpeza
        // automática do cache, e as permissões recém-criadas não seriam encontradas.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (TenantRole::cases() as $tenantRole) {
            Role::findOrCreate($tenantRole->value)->syncPermissions(
                array_map(fn (TenantPermission $permission): string => $permission->value, $tenantRole->permissions()),
            );
        }
    }
}

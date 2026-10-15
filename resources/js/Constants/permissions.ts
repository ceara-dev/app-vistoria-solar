// Permissões por empresa (spatie/laravel-permission com teams). Espelham App\Tenancy\TenantPermission.
export const PERMISSIONS = {
    MEMBERS_VIEW: 'members.view',
    MEMBERS_MANAGE: 'members.manage',
    CUSTOMERS_VIEW: 'customers.view',
    CUSTOMERS_MANAGE: 'customers.manage',
    TRIPS_RECORD: 'trips.record',
} as const;

export type Permission = (typeof PERMISSIONS)[keyof typeof PERMISSIONS];

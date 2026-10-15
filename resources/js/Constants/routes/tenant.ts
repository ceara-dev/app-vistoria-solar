export const ROUTE_NAMES_TENANT = {
    DASHBOARD: 'tenant.dashboard',
    CUSTOMERS: {
        INDEX: 'tenant.customers.index',
        CREATE: 'tenant.customers.create',
        STORE: 'tenant.customers.store',
        SHOW: 'tenant.customers.show',
        EDIT: 'tenant.customers.edit',
        UPDATE: 'tenant.customers.update',
        DESTROY: 'tenant.customers.destroy',
    },
    MEMBERS: {
        INDEX: 'tenant.members.index',
        UPDATE: 'tenant.members.update',
        DESTROY: 'tenant.members.destroy',
    },
    INVITATIONS: {
        STORE: 'tenant.invitations.store',
        DESTROY: 'tenant.invitations.destroy',
    },
} as const;

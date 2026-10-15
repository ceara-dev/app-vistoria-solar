export const ROUTE_NAMES_CENTRAL = {
    DASHBOARD: 'central.dashboard',
    TENANTS: {
        INDEX: 'central.tenants.index',
        CREATE: 'central.tenants.create',
        STORE: 'central.tenants.store',
        SUSPEND: 'central.tenants.suspend',
        ACTIVATE: 'central.tenants.activate',
        EXTEND_TRIAL: 'central.tenants.extend-trial',
    },
    B2B_CLIENTS: {
        INDEX: 'central.b2b-clients.index',
        CREATE: 'central.b2b-clients.create',
        STORE: 'central.b2b-clients.store',
        EDIT: 'central.b2b-clients.edit',
        UPDATE: 'central.b2b-clients.update',
        DESTROY: 'central.b2b-clients.destroy',
    },
    PLANS: {
        INDEX: 'central.plans.index',
        CREATE: 'central.plans.create',
        STORE: 'central.plans.store',
        EDIT: 'central.plans.edit',
        UPDATE: 'central.plans.update',
    },
} as const;

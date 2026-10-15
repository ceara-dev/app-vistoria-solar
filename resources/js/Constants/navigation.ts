import { PERMISSIONS, type Permission } from './permissions';
import { ROUTE_NAMES_CENTRAL, ROUTE_NAMES_TENANT } from './routes';

export interface NavigationItem {
    label: string;
    icon: string;
    route: string;
    activePattern: string;
    permission?: Permission;
}

export const TENANT_NAVIGATION: NavigationItem[] = [
    {
        label: 'Painel',
        icon: 'mdi:view-dashboard-outline',
        route: ROUTE_NAMES_TENANT.DASHBOARD,
        activePattern: ROUTE_NAMES_TENANT.DASHBOARD,
    },
    {
        label: 'Clientes',
        icon: 'mdi:account-box-multiple-outline',
        route: ROUTE_NAMES_TENANT.CUSTOMERS.INDEX,
        activePattern: 'tenant.customers.*',
        permission: PERMISSIONS.CUSTOMERS_VIEW,
    },
    {
        label: 'Membros',
        icon: 'mdi:account-group-outline',
        route: ROUTE_NAMES_TENANT.MEMBERS.INDEX,
        activePattern: 'tenant.members.*',
        permission: PERMISSIONS.MEMBERS_VIEW,
    },
];

export const CENTRAL_NAVIGATION: NavigationItem[] = [
    {
        label: 'Painel',
        icon: 'mdi:view-dashboard-outline',
        route: ROUTE_NAMES_CENTRAL.DASHBOARD,
        activePattern: ROUTE_NAMES_CENTRAL.DASHBOARD,
    },
    {
        label: 'Empresas',
        icon: 'mdi:domain',
        route: ROUTE_NAMES_CENTRAL.TENANTS.INDEX,
        activePattern: 'central.tenants.*',
    },
    {
        label: 'Clientes B2B',
        icon: 'mdi:briefcase-outline',
        route: ROUTE_NAMES_CENTRAL.B2B_CLIENTS.INDEX,
        activePattern: 'central.b2b-clients.*',
    },
    {
        label: 'Planos',
        icon: 'mdi:tag-outline',
        route: ROUTE_NAMES_CENTRAL.PLANS.INDEX,
        activePattern: 'central.plans.*',
    },
];

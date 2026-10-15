import type { SelectOption } from '@/Types/shared';

export type TenantStatusValue = 'trial' | 'active' | 'suspended' | 'canceled';

export interface CentralTenantPlan {
    id: number;
    name: string;
}

export interface CentralTenant {
    id: number;
    name: string;
    document: string | null;
    status: TenantStatusValue;
    status_label: string;
    trial_ends_at: string | null;
    plan: CentralTenantPlan | null;
    b2b_client: CentralTenantPlan | null;
    members_count: number;
    customers_count: number;
    created_at: string;
}

export interface CentralTenantFilters {
    search: string | null;
    status: TenantStatusValue | null;
}

export interface TenantPayload {
    name: string;
    admin_email: string;
    plan_id: number | '';
    b2b_client_id: number | '';
}

export interface TenantFormOptions {
    plans: SelectOption<number>[];
    b2bClients: SelectOption<number>[];
}

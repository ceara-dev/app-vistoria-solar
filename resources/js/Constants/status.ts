import type { TenantStatusValue } from '@/Types/central/tenant';

// Cores do badge por situação da empresa. Os rótulos vêm do backend (TenantStatus::options()).
export const TENANT_STATUS_BADGES: Record<TenantStatusValue, string> = {
    trial: 'badge-info',
    active: 'badge-success',
    suspended: 'badge-warning',
    canceled: 'badge-error',
};

import { router } from '@inertiajs/vue3';
import { MESSAGES, ROUTE_NAMES_CENTRAL } from '@/Constants';
import type { CentralTenant } from '@/Types/central/tenant';

export function useCentralTenantActions() {
    function suspend(tenant: CentralTenant): void {
        if (!confirm(MESSAGES.COMMON.CONFIRM_SUSPEND_TENANT)) {
            return;
        }

        router.put(route(ROUTE_NAMES_CENTRAL.TENANTS.SUSPEND, tenant.id), {}, { preserveScroll: true });
    }

    function activate(tenant: CentralTenant): void {
        if (!confirm(MESSAGES.COMMON.CONFIRM_REACTIVATE_TENANT)) {
            return;
        }

        router.put(route(ROUTE_NAMES_CENTRAL.TENANTS.ACTIVATE, tenant.id), {}, { preserveScroll: true });
    }

    function extendTrial(tenant: CentralTenant): void {
        if (!confirm(MESSAGES.COMMON.CONFIRM_EXTEND_TRIAL)) {
            return;
        }

        router.put(route(ROUTE_NAMES_CENTRAL.TENANTS.EXTEND_TRIAL, tenant.id), {}, { preserveScroll: true });
    }

    return { suspend, activate, extendTrial };
}

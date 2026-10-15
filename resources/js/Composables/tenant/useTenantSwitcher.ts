import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { ROUTE_NAMES_AUTH } from '@/Constants';

export function useTenantSwitcher() {
    const page = usePage();

    const currentTenant = computed(() => page.props.tenant);
    const otherTenants = computed(() => page.props.tenants.filter((tenant) => tenant.id !== page.props.tenant?.id));

    function switchTenant(tenantId: number): void {
        router.put(route(ROUTE_NAMES_AUTH.CURRENT_TENANT_UPDATE), { tenant_id: tenantId });
    }

    return { currentTenant, otherTenants, switchTenant };
}

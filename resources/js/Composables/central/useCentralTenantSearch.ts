import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { ROUTE_NAMES_CENTRAL } from '@/Constants';
import type { TenantStatusValue } from '@/Types/central/tenant';

const SEARCH_DELAY_MS = 300;

/**
 * Busca e filtro de situação da lista de empresas da Central. Os dois
 * refazem a consulta no servidor.
 */
export function useCentralTenantSearch(initialSearch: string | null, initialStatus: TenantStatusValue | null) {
    const search = ref(initialSearch ?? '');
    const status = ref<TenantStatusValue | ''>(initialStatus ?? '');
    let timer: ReturnType<typeof setTimeout> | undefined;

    function apply(): void {
        const params: Record<string, string> = {};

        if (search.value.trim() !== '') {
            params.search = search.value.trim();
        }

        if (status.value !== '') {
            params.status = status.value;
        }

        router.get(route(ROUTE_NAMES_CENTRAL.TENANTS.INDEX), params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }

    watch(search, () => {
        clearTimeout(timer);
        timer = setTimeout(apply, SEARCH_DELAY_MS);
    });

    watch(status, apply);

    return { search, status };
}

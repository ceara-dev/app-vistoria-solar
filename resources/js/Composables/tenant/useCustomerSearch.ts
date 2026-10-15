import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { ROUTE_NAMES_TENANT } from '@/Constants';

const SEARCH_DELAY_MS = 300;

/**
 * Busca da lista de clientes, refeita no servidor enquanto a pessoa digita.
 */
export function useCustomerSearch(initialSearch: string | null) {
    const search = ref(initialSearch ?? '');
    let timer: ReturnType<typeof setTimeout> | undefined;

    watch(search, (value) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            router.get(
                route(ROUTE_NAMES_TENANT.CUSTOMERS.INDEX),
                value.trim() === '' ? {} : { search: value.trim() },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, SEARCH_DELAY_MS);
    });

    return { search };
}

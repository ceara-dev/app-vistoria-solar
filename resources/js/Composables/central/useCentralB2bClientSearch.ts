import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { ROUTE_NAMES_CENTRAL } from '@/Constants';

const SEARCH_DELAY_MS = 300;

/**
 * Busca da lista de clientes B2B, refeita no servidor enquanto a pessoa digita.
 */
export function useCentralB2bClientSearch(initialSearch: string | null) {
    const search = ref(initialSearch ?? '');
    let timer: ReturnType<typeof setTimeout> | undefined;

    watch(search, (value) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            router.get(
                route(ROUTE_NAMES_CENTRAL.B2B_CLIENTS.INDEX),
                value.trim() === '' ? {} : { search: value.trim() },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, SEARCH_DELAY_MS);
    });

    return { search };
}

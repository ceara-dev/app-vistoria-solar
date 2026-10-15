import { router } from '@inertiajs/vue3';
import { MESSAGES, ROUTE_NAMES_CENTRAL } from '@/Constants';
import type { B2bClient } from '@/Types/central/b2bClient';

export function useCentralB2bClientActions() {
    function destroy(client: B2bClient): void {
        if (!confirm(MESSAGES.COMMON.CONFIRM_DELETE_B2B_CLIENT)) {
            return;
        }

        router.delete(route(ROUTE_NAMES_CENTRAL.B2B_CLIENTS.DESTROY, client.id), { preserveScroll: true });
    }

    return { destroy };
}

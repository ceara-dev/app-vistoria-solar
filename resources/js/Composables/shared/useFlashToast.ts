import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Mensagem de retorno enviada pelo backend com `Inertia::flash('toast', ...)`.
 */
export function useFlashToast() {
    const page = usePage();

    const toast = computed(() => page.flash.toast ?? null);

    return { toast };
}

import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ROUTE_NAMES_AUTH } from '@/Constants';

export function useEmailVerification(status: () => string) {
    const form = useForm({});

    const verificationLinkSent = computed(() => status() === 'verification-link-sent');

    function resend(): void {
        form.post(route(ROUTE_NAMES_AUTH.VERIFICATION_SEND));
    }

    return { form, verificationLinkSent, resend };
}

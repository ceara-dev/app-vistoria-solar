import { useForm } from '@inertiajs/vue3';
import { FIELD_LIMITS, ROUTE_NAMES_TENANT } from '@/Constants';
import { email, maxLength, required } from '@/Utils';
import { scrollToFirstError, useFormValidation } from '@/Composables/shared/useFormValidation';
import type { InvitePayload } from '@/Types/tenant/member';

export function useInviteMemberForm() {
    const form = useForm<InvitePayload>({
        email: '',
        role: 'vistoriador',
    });

    const { validateField, validateAll } = useFormValidation(form, {
        email: [required(), email(), maxLength(FIELD_LIMITS.EMAIL_MAX)],
        role: [required()],
    });

    function submit(): void {
        if (!validateAll()) {
            return;
        }

        form.post(route(ROUTE_NAMES_TENANT.INVITATIONS.STORE), {
            preserveScroll: true,
            onError: scrollToFirstError,
            onSuccess: () => form.reset('email'),
        });
    }

    return { form, validateField, submit };
}

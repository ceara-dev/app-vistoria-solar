import { useForm } from '@inertiajs/vue3';
import { FIELD_LIMITS, ROUTE_NAMES_AUTH } from '@/Constants';
import { maxLength, minLength, required, sameAs } from '@/Utils';
import { scrollToFirstError, useFormValidation } from '@/Composables/shared/useFormValidation';
import type { InvitationAcceptanceMode } from '@/Types/auth';

export function useInvitationAcceptance(token: string, mode: InvitationAcceptanceMode) {
    const form = useForm({
        name: '',
        password: '',
        password_confirmation: '',
    });

    // Só quem ainda não tem conta preenche os campos.
    const { validateField, validateAll } = useFormValidation(
        form,
        mode === 'create_account'
            ? {
                  name: [required(), maxLength(FIELD_LIMITS.NAME_MAX)],
                  password: [required(), minLength(FIELD_LIMITS.PASSWORD_MIN)],
                  password_confirmation: [required(), sameAs(() => form.password)],
              }
            : {},
    );

    function accept(): void {
        if (!validateAll()) {
            return;
        }

        form.post(route(ROUTE_NAMES_AUTH.INVITATION_ACCEPT, token), {
            onError: scrollToFirstError,
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }

    return { form, validateField, accept };
}

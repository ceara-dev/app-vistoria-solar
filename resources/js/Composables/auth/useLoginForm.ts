import { useForm } from '@inertiajs/vue3';
import { FIELD_LIMITS, ROUTE_NAMES_AUTH } from '@/Constants';
import { email, maxLength, required } from '@/Utils';
import { scrollToFirstError, useFormValidation } from '@/Composables/shared/useFormValidation';

export function useLoginForm() {
    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const { validateField, validateAll } = useFormValidation(form, {
        email: [required(), email(), maxLength(FIELD_LIMITS.EMAIL_MAX)],
        password: [required()],
    });

    function submit(): void {
        if (!validateAll()) {
            return;
        }

        form.post(route(ROUTE_NAMES_AUTH.LOGIN), {
            onError: scrollToFirstError,
            onFinish: () => form.reset('password'),
        });
    }

    return { form, validateField, submit };
}

import { useForm } from '@inertiajs/vue3';
import { FIELD_LIMITS, ROUTE_NAMES_AUTH } from '@/Constants';
import { email, maxLength, minLength, required, sameAs } from '@/Utils';
import { scrollToFirstError, useFormValidation } from '@/Composables/shared/useFormValidation';

export function useRegisterForm() {
    const form = useForm({
        name: '',
        email: '',
        company_name: '',
        password: '',
        password_confirmation: '',
    });

    const { validateField, validateAll } = useFormValidation(form, {
        name: [required(), maxLength(FIELD_LIMITS.NAME_MAX)],
        email: [required(), email(), maxLength(FIELD_LIMITS.EMAIL_MAX)],
        company_name: [required(), maxLength(FIELD_LIMITS.COMPANY_NAME_MAX)],
        password: [required(), minLength(FIELD_LIMITS.PASSWORD_MIN)],
        password_confirmation: [required(), sameAs(() => form.password)],
    });

    function submit(): void {
        if (!validateAll()) {
            return;
        }

        form.post(route(ROUTE_NAMES_AUTH.REGISTER), {
            onError: scrollToFirstError,
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }

    return { form, validateField, submit };
}

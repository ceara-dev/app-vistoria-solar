import { useForm } from '@inertiajs/vue3';
import { ROUTE_NAMES_AUTH } from '@/Constants';
import { required } from '@/Utils';
import { useFormValidation } from '@/Composables/shared/useFormValidation';

export function useConfirmPasswordForm() {
    const form = useForm({
        password: '',
    });

    const { validateField, validateAll } = useFormValidation(form, {
        password: [required()],
    });

    function submit(): void {
        if (!validateAll()) {
            return;
        }

        form.post(route(ROUTE_NAMES_AUTH.PASSWORD_CONFIRM), {
            onFinish: () => form.reset(),
        });
    }

    return { form, validateField, submit };
}

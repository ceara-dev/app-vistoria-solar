import { useForm } from '@inertiajs/vue3';
import { FIELD_LIMITS, ROUTE_NAMES_CENTRAL } from '@/Constants';
import { email, maxLength, required } from '@/Utils';
import { scrollToFirstError, useFormValidation } from '@/Composables/shared/useFormValidation';
import type { TenantPayload } from '@/Types/central/tenant';

/**
 * Cadastro de empresa pela Central, com o convite ao administrador. As regras
 * espelham o StoreTenantRequest.
 */
export function useCentralTenantForm() {
    const form = useForm<TenantPayload>({
        name: '',
        admin_email: '',
        plan_id: '',
        b2b_client_id: '',
    });

    const { validateField, validateAll } = useFormValidation(form, {
        name: [required(), maxLength(FIELD_LIMITS.NAME_MAX)],
        admin_email: [required(), email(), maxLength(FIELD_LIMITS.EMAIL_MAX)],
    });

    function submit(): void {
        if (!validateAll()) {
            return;
        }

        form.transform((data) => ({
            name: data.name,
            admin_email: data.admin_email,
            plan_id: data.plan_id === '' ? null : Number(data.plan_id),
            b2b_client_id: data.b2b_client_id === '' ? null : Number(data.b2b_client_id),
        })).post(route(ROUTE_NAMES_CENTRAL.TENANTS.STORE), { onError: scrollToFirstError });
    }

    return { form, validateField, submit };
}

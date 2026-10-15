import { router, useForm } from '@inertiajs/vue3';
import { FIELD_LIMITS, ROUTE_NAMES_AUTH } from '@/Constants';
import { maxLength, required } from '@/Utils';
import { scrollToFirstError, useFormValidation } from '@/Composables/shared/useFormValidation';

export function useTenantSelection() {
    const form = useForm({
        company_name: '',
    });

    const { validateField, validateAll } = useFormValidation(form, {
        company_name: [required(), maxLength(FIELD_LIMITS.COMPANY_NAME_MAX)],
    });

    function chooseTenant(tenantId: number): void {
        router.put(route(ROUTE_NAMES_AUTH.CURRENT_TENANT_UPDATE), { tenant_id: tenantId });
    }

    function createTenant(): void {
        if (!validateAll()) {
            return;
        }

        form.post(route(ROUTE_NAMES_AUTH.TENANT_STORE), { onError: scrollToFirstError });
    }

    return { form, validateField, chooseTenant, createTenant };
}

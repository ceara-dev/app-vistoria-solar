import { useForm } from '@inertiajs/vue3';
import { FIELD_LIMITS, ROUTE_NAMES_CENTRAL } from '@/Constants';
import { integer, maxLength, minValue, number, required } from '@/Utils';
import { scrollToFirstError, useFormValidation } from '@/Composables/shared/useFormValidation';
import type { Plan, PlanPayload } from '@/Types/central/plan';

const integerOrNull = (value: string | number): number | null => (String(value).trim() === '' ? null : Number(value));

function payloadFrom(plan?: Plan): PlanPayload {
    return {
        name: plan?.name ?? '',
        price: plan ? (plan.price_cents / 100).toFixed(2) : '',
        limits: {
            users: plan?.limits?.users?.toString() ?? '',
            trips_per_month: plan?.limits?.trips_per_month?.toString() ?? '',
            storage_mb: plan?.limits?.storage_mb?.toString() ?? '',
        },
        is_active: plan?.is_active ?? true,
    };
}

/**
 * Cadastro e edição de plano. O preço é digitado em reais; o envio converte
 * para centavos, e os limites vazios viram `null` ("Ilimitado").
 */
export function useCentralPlanForm(plan?: Plan) {
    const form = useForm<PlanPayload>(payloadFrom(plan));

    const { validateField, validateAll } = useFormValidation(form, {
        name: [required(), maxLength(FIELD_LIMITS.NAME_MAX)],
        price: [required(), number(), minValue(0)],
        'limits.users': [integer(), minValue(1)],
        'limits.trips_per_month': [integer(), minValue(1)],
        'limits.storage_mb': [integer(), minValue(1)],
    });

    function submit(): void {
        if (!validateAll()) {
            return;
        }

        form.transform((data) => ({
            name: data.name,
            price_cents: Math.round(Number(data.price) * 100),
            limits: {
                users: integerOrNull(data.limits.users),
                trips_per_month: integerOrNull(data.limits.trips_per_month),
                storage_mb: integerOrNull(data.limits.storage_mb),
            },
            is_active: data.is_active,
        }));

        const options = { onError: scrollToFirstError };

        if (plan) {
            form.put(route(ROUTE_NAMES_CENTRAL.PLANS.UPDATE, plan.id), options);
        } else {
            form.post(route(ROUTE_NAMES_CENTRAL.PLANS.STORE), options);
        }
    }

    return { form, validateField, submit };
}

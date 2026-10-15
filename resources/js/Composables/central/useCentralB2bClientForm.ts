import { useForm } from '@inertiajs/vue3';
import { FIELD_LIMITS, ROUTE_NAMES_CENTRAL } from '@/Constants';
import { cnpj, email, formatCnpj, formatPhone, maxLength, phoneBr, required } from '@/Utils';
import { scrollToFirstError, useFormValidation } from '@/Composables/shared/useFormValidation';
import type { B2bClient, B2bClientPayload } from '@/Types/central/b2bClient';

function payloadFrom(client?: B2bClient): B2bClientPayload {
    return {
        legal_name: client?.legal_name ?? '',
        trade_name: client?.trade_name ?? '',
        document: client?.document ? formatCnpj(client.document) : '',
        contact_name: client?.contact_name ?? '',
        contact_email: client?.contact_email ?? '',
        contact_phone: client?.contact_phone ? formatPhone(client.contact_phone) : '',
    };
}

/**
 * Cadastro e edição de cliente B2B. As regras espelham o StoreB2bClientRequest.
 */
export function useCentralB2bClientForm(client?: B2bClient) {
    const form = useForm<B2bClientPayload>(payloadFrom(client));

    const { validateField, validateAll } = useFormValidation(form, {
        legal_name: [required(), maxLength(FIELD_LIMITS.NAME_MAX)],
        trade_name: [maxLength(FIELD_LIMITS.TRADE_NAME_MAX)],
        document: [cnpj()],
        contact_name: [maxLength(FIELD_LIMITS.NAME_MAX)],
        contact_email: [email(), maxLength(FIELD_LIMITS.EMAIL_MAX)],
        contact_phone: [phoneBr()],
    });

    function submit(): void {
        if (!validateAll()) {
            return;
        }

        const options = { onError: scrollToFirstError };

        if (client) {
            form.put(route(ROUTE_NAMES_CENTRAL.B2B_CLIENTS.UPDATE, client.id), options);
        } else {
            form.post(route(ROUTE_NAMES_CENTRAL.B2B_CLIENTS.STORE), options);
        }
    }

    return { form, validateField, submit };
}

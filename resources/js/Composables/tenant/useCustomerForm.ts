import { useForm } from '@inertiajs/vue3';
import { FIELD_LIMITS, ROUTE_NAMES_TENANT } from '@/Constants';
import { documentFor, email, formatDocument, formatPhone, formatZipCode, maxLength, phoneBr, required, zipCode } from '@/Utils';
import { scrollToFirstError, useFormValidation } from '@/Composables/shared/useFormValidation';
import type { Customer, CustomerAddressPayload, CustomerContactPayload, CustomerPayload } from '@/Types/tenant/customer';

const emptyAddress = (): CustomerAddressPayload => ({
    id: null,
    label: '',
    zip_code: '',
    street: '',
    number: '',
    complement: '',
    district: '',
    city: '',
    state: '',
});

const emptyContact = (): CustomerContactPayload => ({ id: null, name: '', position: '', email: '', phone: '' });

function payloadFrom(customer?: Customer): CustomerPayload {
    return {
        type: customer?.type ?? 'PF',
        name: customer?.name ?? '',
        trade_name: customer?.trade_name ?? '',
        document: customer?.document ? formatDocument(customer.type, customer.document) : '',
        email: customer?.email ?? '',
        phone: customer?.phone ? formatPhone(customer.phone) : '',
        notes: customer?.notes ?? '',
        addresses: (customer?.addresses ?? []).map((address) => ({
            ...address,
            label: address.label ?? '',
            complement: address.complement ?? '',
            zip_code: formatZipCode(address.zip_code),
        })),
        contacts: (customer?.contacts ?? []).map((contact) => ({
            id: contact.id,
            name: contact.name,
            position: contact.position ?? '',
            email: contact.email ?? '',
            phone: contact.phone ? formatPhone(contact.phone) : '',
        })),
    };
}

/**
 * Cadastro e edição de cliente, com endereços e contatos. As regras espelham o StoreCustomerRequest.
 */
export function useCustomerForm(customer?: Customer) {
    const form = useForm<CustomerPayload>(payloadFrom(customer));

    const { validateField, validateAll } = useFormValidation(form, {
        type: [required()],
        name: [required(), maxLength(FIELD_LIMITS.CUSTOMER_NAME_MAX)],
        trade_name: [maxLength(FIELD_LIMITS.TRADE_NAME_MAX)],
        document: [documentFor(() => form.type)],
        email: [email(), maxLength(FIELD_LIMITS.EMAIL_MAX)],
        phone: [phoneBr()],
        notes: [maxLength(FIELD_LIMITS.NOTES_MAX)],
        'addresses.*.label': [maxLength(FIELD_LIMITS.ADDRESS_LABEL_MAX)],
        'addresses.*.zip_code': [required(), zipCode()],
        'addresses.*.street': [required(), maxLength(FIELD_LIMITS.STREET_MAX)],
        'addresses.*.number': [required(), maxLength(FIELD_LIMITS.NUMBER_MAX)],
        'addresses.*.complement': [maxLength(FIELD_LIMITS.COMPLEMENT_MAX)],
        'addresses.*.district': [required(), maxLength(FIELD_LIMITS.DISTRICT_MAX)],
        'addresses.*.city': [required(), maxLength(FIELD_LIMITS.CITY_MAX)],
        'addresses.*.state': [required()],
        'contacts.*.name': [required(), maxLength(FIELD_LIMITS.CONTACT_NAME_MAX)],
        'contacts.*.position': [maxLength(FIELD_LIMITS.CONTACT_POSITION_MAX)],
        'contacts.*.email': [email(), maxLength(FIELD_LIMITS.EMAIL_MAX)],
        'contacts.*.phone': [phoneBr()],
    });

    const errorAt = (path: string): string | undefined => (form.errors as Record<string, string | undefined>)[path];

    /**
     * Depois de remover um item, os índices mudam e os erros da lista não valem mais.
     */
    function clearListErrors(list: 'addresses' | 'contacts'): void {
        form.clearErrors(...(Object.keys(form.errors).filter((key) => key.startsWith(`${list}.`)) as never[]));
    }

    function addAddress(): void {
        form.addresses.push(emptyAddress());
    }

    function removeAddress(index: number): void {
        form.addresses.splice(index, 1);
        clearListErrors('addresses');
    }

    function addContact(): void {
        form.contacts.push(emptyContact());
    }

    function removeContact(index: number): void {
        form.contacts.splice(index, 1);
        clearListErrors('contacts');
    }

    function submit(): void {
        if (!validateAll()) {
            return;
        }

        const options = { onError: scrollToFirstError };

        if (customer) {
            form.put(route(ROUTE_NAMES_TENANT.CUSTOMERS.UPDATE, customer.id), options);
        } else {
            form.post(route(ROUTE_NAMES_TENANT.CUSTOMERS.STORE), options);
        }
    }

    return { form, errorAt, validateField, addAddress, removeAddress, addContact, removeContact, submit };
}

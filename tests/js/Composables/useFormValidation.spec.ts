import { reactive } from 'vue';
import { describe, expect, it } from 'vitest';
import { useFormValidation } from '@/Composables/shared/useFormValidation';
import { email, required } from '@/Utils';

/**
 * Imita o pedaço do `useForm` do Inertia que a validação usa.
 */
function makeForm(fields: { name: string; email: string }) {
    const errors: Partial<Record<'name' | 'email', string>> = reactive({});

    return reactive({
        ...fields,
        errors,
        setError(field: 'name' | 'email', message: string) {
            errors[field] = message;
        },
        clearErrors(...fields: ('name' | 'email')[]) {
            fields.forEach((field) => delete errors[field]);
        },
    });
}

describe('useFormValidation', () => {
    it('stores the first failing rule message for a field', () => {
        const form = makeForm({ name: '', email: '' });
        const { validateField } = useFormValidation(form, { email: [required(), email()] });

        const isValid = validateField('email');

        expect(isValid).toBe(false);
        expect(form.errors.email).toBe('Este campo é obrigatório');
    });

    it('clears the error once the field becomes valid', () => {
        const form = makeForm({ name: '', email: 'pessoa@' });
        const { validateField } = useFormValidation(form, { email: [required(), email()] });
        validateField('email');

        form.email = 'pessoa@empresa.com.br';
        validateField('email');

        expect(form.errors.email).toBeUndefined();
    });

    it('validates every field with rules before submitting', () => {
        const form = makeForm({ name: '', email: 'pessoa@' });
        const { validateAll } = useFormValidation(form, { name: [required()], email: [email()] });

        const isValid = validateAll();

        expect(isValid).toBe(false);
        expect(form.errors).toEqual({ name: 'Este campo é obrigatório', email: 'E-mail inválido' });
    });

    it('passes when every field is valid', () => {
        const form = makeForm({ name: 'Ana', email: 'ana@empresa.com.br' });
        const { validateAll } = useFormValidation(form, { name: [required()], email: [email()] });

        expect(validateAll()).toBe(true);
    });
});

describe('useFormValidation with lists', () => {
    function makeListForm(addresses: { street: string }[]) {
        const errors: Record<string, string> = reactive({});

        return reactive({
            addresses,
            errors,
            setError(field: string, message: string) {
                errors[field] = message;
            },
            clearErrors(...fields: string[]) {
                fields.forEach((field) => delete errors[field]);
            },
        });
    }

    it('validates every item of a list with the wildcard rule', () => {
        const form = makeListForm([{ street: 'Rua A' }, { street: '' }]);
        const { validateAll } = useFormValidation(form, { 'addresses.*.street': [required()] });

        expect(validateAll()).toBe(false);
        expect(form.errors).toEqual({ 'addresses.1.street': 'Este campo é obrigatório' });
    });

    it('validates a single item field by its concrete path', () => {
        const form = makeListForm([{ street: '' }]);
        const { validateField } = useFormValidation(form, { 'addresses.*.street': [required()] });

        validateField('addresses.0.street');

        expect(form.errors['addresses.0.street']).toBe('Este campo é obrigatório');
    });

    it('passes when the list is empty', () => {
        const form = makeListForm([]);
        const { validateAll } = useFormValidation(form, { 'addresses.*.street': [required()] });

        expect(validateAll()).toBe(true);
    });
});

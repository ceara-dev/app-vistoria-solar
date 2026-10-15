import { nextTick } from 'vue';
import type { FieldRule } from '@/Utils';

/**
 * O mínimo de um formulário do Inertia (`useForm`) que a validação usa.
 * Os erros do front e os devolvidos pelo backend ficam no mesmo `errors`,
 * com as mesmas chaves (`addresses.0.street`).
 */
export type ValidatableForm<TFields extends object> = TFields & {
    setError(field: string, message: string): unknown;
    clearErrors(...fields: string[]): unknown;
};

/**
 * Regras por campo. Listas usam `*`, como no Laravel: `addresses.*.street`.
 */
export type FormRules = Record<string, FieldRule[]>;

const WILDCARD = '*';

function valueAt(source: unknown, path: string): unknown {
    return path
        .split('.')
        .reduce<unknown>((value, key) => (value === null || value === undefined ? undefined : (value as Record<string, unknown>)[key]), source);
}

/**
 * `addresses.*.street` vira `addresses.0.street`, `addresses.1.street`... conforme os itens atuais.
 */
function expandPath(source: unknown, segments: string[], prefix: string[] = []): string[] {
    const wildcardAt = segments.indexOf(WILDCARD);

    if (wildcardAt === -1) {
        return [[...prefix, ...segments].join('.')];
    }

    const listPath = [...prefix, ...segments.slice(0, wildcardAt)];
    const items = valueAt(source, listPath.join('.'));

    if (!Array.isArray(items)) {
        return [];
    }

    return items.flatMap((_, index) => expandPath(source, segments.slice(wildcardAt + 1), [...listPath, String(index)]));
}

/**
 * `addresses.0.street` vira `addresses.*.street`, a chave da regra.
 */
function patternOf(path: string): string {
    return path
        .split('.')
        .map((segment) => (/^\d+$/.test(segment) ? WILDCARD : segment))
        .join('.');
}

/**
 * Leva a tela até o primeiro campo com erro, marcado com `aria-invalid`.
 */
export function scrollToFirstError(): void {
    if (typeof document === 'undefined') {
        return;
    }

    nextTick(() => {
        document.querySelector('[aria-invalid="true"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}

/**
 * Validação espelhada do contrato (.docs/VALIDATION.md): valida um campo no `blur`
 * e todos antes do envio.
 */
export function useFormValidation<TFields extends object>(form: ValidatableForm<TFields>, rules: FormRules) {
    function validateField(path: string): boolean {
        const fieldRules = rules[path] ?? rules[patternOf(path)] ?? [];
        const message = fieldRules.map((rule) => rule(valueAt(form, path))).find((error) => error !== null);

        if (message) {
            form.setError(path, message);

            return false;
        }

        form.clearErrors(path);

        return true;
    }

    function validateAll(): boolean {
        const paths = Object.keys(rules).flatMap((pattern) => expandPath(form, pattern.split('.')));
        const isValid = paths.map((path) => validateField(path)).every(Boolean);

        if (!isValid) {
            scrollToFirstError();
        }

        return isValid;
    }

    return { validateField, validateAll };
}

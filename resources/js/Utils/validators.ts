import { MESSAGES } from '@/Constants';
import { fillPlaceholders, onlyDigits } from './string';

/**
 * Regra de campo: devolve a mensagem de erro ou `null` quando o valor é válido.
 */
export type FieldRule = (value: unknown) => string | null;

const CNPJ_FIRST_DIGIT_WEIGHTS = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
const CNPJ_SECOND_DIGIT_WEIGHTS = [6, ...CNPJ_FIRST_DIGIT_WEIGHTS];

export const isRequired = (value: unknown): boolean =>
    value !== null && value !== undefined && String(value).trim() !== '';

export const isEmail = (value: string): boolean => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

const hasRepeatedDigits = (digits: string): boolean => new Set(digits).size === 1;

/**
 * Mesmo cálculo de App\Rules\Cpf. Aceita o número com ou sem pontuação.
 */
export const isCpf = (value: string): boolean => {
    const cpf = onlyDigits(value);

    if (cpf.length !== 11 || hasRepeatedDigits(cpf)) {
        return false;
    }

    return [9, 10].every((position) => {
        const sum = [...cpf.slice(0, position)].reduce((total, digit, index) => total + Number(digit) * (position + 1 - index), 0);

        return Number(cpf[position]) === ((sum * 10) % 11) % 10;
    });
};

/**
 * Mesmo cálculo de App\Rules\Cnpj. Aceita o número com ou sem pontuação.
 */
export const isCnpj = (value: string): boolean => {
    const cnpj = onlyDigits(value);

    if (cnpj.length !== 14 || hasRepeatedDigits(cnpj)) {
        return false;
    }

    return [CNPJ_FIRST_DIGIT_WEIGHTS, CNPJ_SECOND_DIGIT_WEIGHTS].every((weights) => {
        const sum = weights.reduce((total, weight, index) => total + Number(cnpj[index]) * weight, 0);
        const remainder = sum % 11;

        return Number(cnpj[weights.length]) === (remainder < 2 ? 0 : 11 - remainder);
    });
};

/**
 * Mesmo padrão de App\Rules\PhoneBr: DDD mais fixo (8 dígitos) ou celular (9 dígitos começando por 9).
 */
export const isPhoneBr = (value: string): boolean => /^[1-9][1-9](9\d{8}|[2-8]\d{7})$/.test(onlyDigits(value));

export const isZipCode = (value: string): boolean => {
    const zipCode = onlyDigits(value);

    return zipCode.length === 8 && zipCode !== '00000000';
};

const isBlank = (value: unknown): boolean => !isRequired(value);

/**
 * Regra que só roda com o campo preenchido. Campo vazio fica a cargo de `required()`.
 */
const whenFilled =
    (check: (value: string) => boolean, message: string): FieldRule =>
    (value) =>
        isBlank(value) || check(String(value)) ? null : message;

export const required = (): FieldRule => (value) => (isRequired(value) ? null : MESSAGES.VALIDATION.REQUIRED);

export const email = (): FieldRule => whenFilled(isEmail, MESSAGES.VALIDATION.INVALID_EMAIL);

export const maxLength = (max: number): FieldRule =>
    whenFilled((value) => value.length <= max, fillPlaceholders(MESSAGES.VALIDATION.MAX_LENGTH, { n: max }));

export const minLength = (min: number): FieldRule =>
    whenFilled((value) => value.length >= min, fillPlaceholders(MESSAGES.VALIDATION.MIN_LENGTH, { n: min }));

export const cpf = (): FieldRule => whenFilled(isCpf, MESSAGES.VALIDATION.INVALID_CPF);

export const cnpj = (): FieldRule => whenFilled(isCnpj, MESSAGES.VALIDATION.INVALID_CNPJ);

export const phoneBr = (): FieldRule => whenFilled(isPhoneBr, MESSAGES.VALIDATION.INVALID_PHONE);

export const zipCode = (): FieldRule => whenFilled(isZipCode, MESSAGES.VALIDATION.INVALID_ZIP_CODE);

export const number = (): FieldRule => whenFilled((value) => Number.isFinite(Number(value)), MESSAGES.VALIDATION.INVALID_NUMBER);

export const integer = (): FieldRule => whenFilled((value) => Number.isInteger(Number(value)), MESSAGES.VALIDATION.INVALID_NUMBER);

export const minValue = (min: number): FieldRule =>
    whenFilled((value) => Number(value) >= min, fillPlaceholders(MESSAGES.VALIDATION.MIN_VALUE, { n: min }));

/**
 * CPF ou CNPJ conforme o tipo de pessoa, como o StoreCustomerRequest.
 */
export const documentFor =
    (type: () => 'PF' | 'PJ'): FieldRule =>
    (value) =>
        (type() === 'PJ' ? cnpj() : cpf())(value);

/**
 * O valor precisa ser igual ao de outro campo (equivale ao `confirmed` do Laravel).
 */
export const sameAs =
    (otherValue: () => unknown): FieldRule =>
    (value) =>
        value === otherValue() ? null : MESSAGES.VALIDATION.CONFIRMED;

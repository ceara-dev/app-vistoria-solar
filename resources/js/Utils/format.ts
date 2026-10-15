import { onlyDigits } from './string';

export const formatDate = (date: string | Date): string => new Intl.DateTimeFormat('pt-BR').format(new Date(date));

/**
 * Centavos para o formato da moeda, como `19900` → `R$ 199,00`.
 */
export const formatCurrency = (cents: number): string =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(cents / 100);

export const formatCpf = (cpf: string): string => onlyDigits(cpf).replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');

export const formatCnpj = (cnpj: string): string =>
    onlyDigits(cnpj).replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');

/**
 * CPF para pessoa física, CNPJ para jurídica. Valores fora do padrão saem como vieram.
 */
export const formatDocument = (type: 'PF' | 'PJ', document: string): string =>
    type === 'PJ' ? formatCnpj(document) : formatCpf(document);

export const formatPhone = (phone: string): string =>
    onlyDigits(phone).replace(/^(\d{2})(\d{4,5})(\d{4})$/, '($1) $2-$3');

export const formatZipCode = (zipCode: string): string => onlyDigits(zipCode).replace(/^(\d{5})(\d{3})$/, '$1-$2');

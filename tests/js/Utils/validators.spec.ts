import { describe, expect, it } from 'vitest';
import { email, integer, maxLength, minLength, minValue, number, required, sameAs } from '@/Utils';

describe('required', () => {
    it.each([[''], ['   '], [null], [undefined]])('rejects the blank value %j with the contract message', (value) => {
        expect(required()(value)).toBe('Este campo é obrigatório');
    });

    it('accepts a filled value', () => {
        expect(required()('Vistoria')).toBeNull();
    });
});

describe('email', () => {
    it('rejects an address without a domain', () => {
        expect(email()('pessoa@')).toBe('E-mail inválido');
    });

    it('accepts a valid address', () => {
        expect(email()('pessoa@empresa.com.br')).toBeNull();
    });

    it('leaves blank values to the required rule', () => {
        expect(email()('')).toBeNull();
    });
});

describe('maxLength', () => {
    it('rejects a value above the limit with the limit in the message', () => {
        expect(maxLength(5)('123456')).toBe('Máximo de 5 caracteres');
    });

    it('accepts a value at the limit', () => {
        expect(maxLength(5)('12345')).toBeNull();
    });
});

describe('minLength', () => {
    it('rejects a value below the limit with the limit in the message', () => {
        expect(minLength(8)('1234567')).toBe('Mínimo de 8 caracteres');
    });

    it('accepts a value at the limit', () => {
        expect(minLength(8)('12345678')).toBeNull();
    });
});

describe('sameAs', () => {
    it('rejects a confirmation that differs from the original value', () => {
        expect(sameAs(() => 'senha-forte')('outra-senha')).toBe('A confirmação não confere');
    });

    it('accepts an identical confirmation', () => {
        expect(sameAs(() => 'senha-forte')('senha-forte')).toBeNull();
    });
});

describe('number', () => {
    it('rejects text that is not a number', () => {
        expect(number()('muito')).toBe('Valor inválido');
    });

    it('accepts integers, decimals and zero', () => {
        expect(number()('199')).toBeNull();
        expect(number()('0.99')).toBeNull();
        expect(number()('0')).toBeNull();
    });

    it('leaves blank values to the required rule', () => {
        expect(number()('')).toBeNull();
    });
});

describe('integer', () => {
    it('rejects decimals with the number message', () => {
        expect(integer()('1.5')).toBe('Valor inválido');
    });

    it('accepts a whole number', () => {
        expect(integer()('20')).toBeNull();
    });
});

describe('minValue', () => {
    it('rejects a value below the minimum with the minimum in the message', () => {
        expect(minValue(1)('0')).toBe('Valor mínimo: 1');
    });

    it('accepts a value at the minimum', () => {
        expect(minValue(0)('0')).toBeNull();
    });
});

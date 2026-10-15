import { describe, expect, it } from 'vitest';
import { formatCnpj, formatCpf, formatCurrency, formatDocument, formatPhone, formatZipCode } from '@/Utils';

describe('formatters', () => {
    it('format documents by type', () => {
        expect(formatCpf('52998224725')).toBe('529.982.247-25');
        expect(formatCnpj('11222333000181')).toBe('11.222.333/0001-81');
        expect(formatDocument('PJ', '11222333000181')).toBe('11.222.333/0001-81');
    });

    it('format landlines and mobiles', () => {
        expect(formatPhone('1133334444')).toBe('(11) 3333-4444');
        expect(formatPhone('11987654321')).toBe('(11) 98765-4321');
    });

    it('format the zip code', () => {
        expect(formatZipCode('01310100')).toBe('01310-100');
    });

    it('keep values outside the pattern as they are', () => {
        expect(formatCpf('123')).toBe('123');
    });

    // O Intl separa o símbolo do valor com espaço inquebrável (U+00A0).
    it('format cents as Brazilian currency', () => {
        expect(formatCurrency(19900)).toBe('R$\u00A0199,00');
        expect(formatCurrency(0)).toBe('R$\u00A00,00');
        expect(formatCurrency(123456)).toBe('R$\u00A01.234,56');
    });
});

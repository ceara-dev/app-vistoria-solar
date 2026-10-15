import { describe, expect, it } from 'vitest';
import { documentFor, isCnpj, isCpf, isPhoneBr, isZipCode, phoneBr, zipCode } from '@/Utils';

// Os mesmos casos de tests/Feature/Rules: front e backend precisam concordar.
describe('isCpf', () => {
    it.each(['52998224725', '111.444.777-35'])('accepts the valid CPF %s', (cpf) => {
        expect(isCpf(cpf)).toBe(true);
    });

    it.each([
        ['wrong check digit', '52998224724'],
        ['all digits equal', '11111111111'],
        ['ten digits', '5299822472'],
    ])('rejects a CPF with %s', (_, cpf) => {
        expect(isCpf(cpf)).toBe(false);
    });
});

describe('isCnpj', () => {
    it.each(['11222333000181', '04.252.011/0001-10'])('accepts the valid CNPJ %s', (cnpj) => {
        expect(isCnpj(cnpj)).toBe(true);
    });

    it.each([
        ['wrong check digit', '11222333000180'],
        ['all digits equal', '00000000000000'],
        ['a CPF', '52998224725'],
    ])('rejects a CNPJ with %s', (_, cnpj) => {
        expect(isCnpj(cnpj)).toBe(false);
    });
});

describe('isPhoneBr', () => {
    it.each(['1133334444', '(11) 98765-4321'])('accepts the phone %s', (phone) => {
        expect(isPhoneBr(phone)).toBe(true);
    });

    it.each([
        ['no area code', '987654321'],
        ['area code starting with zero', '0187654321'],
        ['eleven digits without the mobile 9', '11887654321'],
    ])('rejects a phone with %s', (_, phone) => {
        expect(isPhoneBr(phone)).toBe(false);
    });
});

describe('isZipCode', () => {
    it('accepts a zip code with or without the dash', () => {
        expect(isZipCode('01310-100')).toBe(true);
        expect(isZipCode('01310100')).toBe(true);
    });

    it.each(['0131010', '00000000'])('rejects the zip code %s', (zip) => {
        expect(isZipCode(zip)).toBe(false);
    });
});

describe('field rules', () => {
    it('validate the document by the customer type', () => {
        let type: 'PF' | 'PJ' = 'PF';
        const documentRule = documentFor(() => type);

        expect(documentRule('11222333000181')).toBe('CPF inválido');

        type = 'PJ';

        expect(documentRule('11222333000181')).toBeNull();
        expect(documentRule('52998224725')).toBe('CNPJ inválido');
    });

    it('leave empty optional fields alone', () => {
        expect(phoneBr()('')).toBeNull();
        expect(documentFor(() => 'PF')('')).toBeNull();
    });

    it('use the contract messages', () => {
        expect(phoneBr()('123')).toBe('Telefone inválido');
        expect(zipCode()('123')).toBe('CEP inválido');
    });
});

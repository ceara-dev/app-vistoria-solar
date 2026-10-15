import { describe, expect, it } from 'vitest';
import { fillPlaceholders, initials } from '@/Utils';

describe('fillPlaceholders', () => {
    it('replaces each placeholder with its value', () => {
        expect(fillPlaceholders('Máximo de {n} caracteres', { n: 255 })).toBe('Máximo de 255 caracteres');
    });

    it('keeps placeholders without a value', () => {
        expect(fillPlaceholders('Data deve ser igual ou após {min}', {})).toBe('Data deve ser igual ou após {min}');
    });
});

describe('initials', () => {
    it('joins the first and the last name', () => {
        expect(initials('José Carlos de Souza')).toBe('JS');
    });

    it('uses the single name when there is only one', () => {
        expect(initials('Equipe')).toBe('E');
    });

    it('ignores extra spaces and returns nothing for a blank name', () => {
        expect(initials('  Maria   Silva ')).toBe('MS');
        expect(initials('   ')).toBe('');
    });
});

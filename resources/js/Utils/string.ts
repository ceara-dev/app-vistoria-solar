/**
 * Troca os marcadores `{nome}` do texto pelos valores informados.
 */
export const fillPlaceholders = (template: string, values: Record<string, string | number>): string =>
    template.replace(/\{(\w+)\}/g, (placeholder, key: string) => (key in values ? String(values[key]) : placeholder));

export const onlyDigits = (value: string): string => value.replace(/\D/g, '');

/**
 * Iniciais para o avatar: primeiro e último nome, como "José Souza" → "JS".
 */
export const initials = (name: string): string => {
    const parts = name.trim().split(/\s+/).filter(Boolean);

    if (parts.length === 0) {
        return '';
    }

    const first = parts[0][0];
    const last = parts.length > 1 ? parts[parts.length - 1][0] : '';

    return (first + last).toUpperCase();
};

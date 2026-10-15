// Limites dos campos, iguais às regras dos FormRequests (max:n, Password::defaults()).
export const FIELD_LIMITS = {
    NAME_MAX: 255,
    EMAIL_MAX: 255,
    COMPANY_NAME_MAX: 255,
    PASSWORD_MIN: 8,
    CUSTOMER_NAME_MAX: 255,
    TRADE_NAME_MAX: 255,
    NOTES_MAX: 2000,
    ADDRESS_LABEL_MAX: 100,
    STREET_MAX: 255,
    NUMBER_MAX: 20,
    COMPLEMENT_MAX: 255,
    DISTRICT_MAX: 255,
    CITY_MAX: 255,
    CONTACT_NAME_MAX: 255,
    CONTACT_POSITION_MAX: 100,
} as const;

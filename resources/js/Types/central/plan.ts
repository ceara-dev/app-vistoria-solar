export interface PlanLimits {
    users: number | null;
    trips_per_month: number | null;
    storage_mb: number | null;
}

export interface Plan {
    id: number;
    name: string;
    price_cents: number;
    limits: PlanLimits | null;
    is_active: boolean;
    tenants_count?: number;
    created_at: string;
}

/**
 * Formato do formulário: o preço em reais e os limites como número digitado,
 * convertidos no envio para `price_cents` e inteiros (ou nulo). Campos
 * `type="number"` com `v-model` devolvem número, e vazio continua texto.
 */
export interface PlanPayload {
    name: string;
    price: string | number;
    limits: {
        users: string | number;
        trips_per_month: string | number;
        storage_mb: string | number;
    };
    is_active: boolean;
}

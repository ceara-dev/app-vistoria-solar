export interface B2bClient {
    id: number;
    legal_name: string;
    trade_name: string | null;
    document: string | null;
    contact_name: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    tenants_count?: number;
    created_at: string;
}

export interface B2bClientFilters {
    search: string | null;
}

export interface B2bClientPayload {
    legal_name: string;
    trade_name: string;
    document: string;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
}

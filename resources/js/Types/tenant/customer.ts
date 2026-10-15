import type { SelectOption } from '@/Types/shared';

export type CustomerTypeValue = 'PF' | 'PJ';

export interface CustomerAddress {
    id: number;
    label: string | null;
    zip_code: string;
    street: string;
    number: string;
    complement: string | null;
    district: string;
    city: string;
    state: string;
}

export interface CustomerContact {
    id: number;
    name: string;
    position: string | null;
    email: string | null;
    phone: string | null;
}

export interface Customer {
    id: number;
    type: CustomerTypeValue;
    type_label: string;
    name: string;
    trade_name: string | null;
    document: string | null;
    email: string | null;
    phone: string | null;
    notes: string | null;
    addresses?: CustomerAddress[];
    contacts?: CustomerContact[];
    created_at: string;
}

export interface CustomerAddressPayload {
    id: number | null;
    label: string;
    zip_code: string;
    street: string;
    number: string;
    complement: string;
    district: string;
    city: string;
    state: string;
}

export interface CustomerContactPayload {
    id: number | null;
    name: string;
    position: string;
    email: string;
    phone: string;
}

export interface CustomerPayload {
    type: CustomerTypeValue;
    name: string;
    trade_name: string;
    document: string;
    email: string;
    phone: string;
    notes: string;
    addresses: CustomerAddressPayload[];
    contacts: CustomerContactPayload[];
}

export interface CustomerFormOptions {
    types: SelectOption<CustomerTypeValue>[];
    states: SelectOption[];
}

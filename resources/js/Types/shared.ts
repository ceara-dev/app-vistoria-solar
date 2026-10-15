export interface AuthUser {
    id: number;
    name: string;
    email: string;
}

export interface TenantSummary {
    id: number;
    name: string;
}

export interface Toast {
    type: 'success' | 'error';
    message: string;
}

export interface SelectOption<TValue = string> {
    value: TValue;
    label: string;
}

/**
 * Props compartilhadas pelo HandleInertiaRequests em todas as páginas.
 */
export interface SharedPageProps {
    auth: {
        user: AuthUser | null;
    };
    tenant: TenantSummary | null;
    tenants: TenantSummary[];
    can: string[];
}

/**
 * Formato de `JsonResource::collection()` quando enviado como prop do Inertia.
 */
export interface ResourceCollection<T> {
    data: T[];
}

/**
 * Formato de `new JsonResource($model)` quando enviado como prop do Inertia.
 */
export interface ResourceItem<T> {
    data: T;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

/**
 * Formato de `JsonResource::collection($paginator)`.
 */
export interface Paginated<T> extends ResourceCollection<T> {
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: PaginationLink[];
    };
}

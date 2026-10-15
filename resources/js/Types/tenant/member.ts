import type { SelectOption } from '@/Types/shared';

export type TenantRoleValue = 'proprietario' | 'administrador' | 'gestor' | 'vistoriador';

export interface Member {
    id: number;
    name: string;
    email: string;
    role: TenantRoleValue | null;
    role_label: string | null;
    is_active: boolean;
    is_current_user: boolean;
}

export interface Invitation {
    id: number;
    email: string;
    role: TenantRoleValue;
    role_label: string;
    expires_at: string;
}

export type RoleOption = SelectOption<TenantRoleValue>;

export interface InvitePayload {
    email: string;
    role: TenantRoleValue;
}

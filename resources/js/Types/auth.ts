export type InvitationAcceptanceMode = 'create_account' | 'login' | 'accept' | 'wrong_account';

export interface InvitationSummary {
    email: string;
    tenant_name: string;
    role_label: string;
}

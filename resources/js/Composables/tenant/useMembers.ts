import { router } from '@inertiajs/vue3';
import { MESSAGES, ROUTE_NAMES_TENANT } from '@/Constants';
import type { Invitation, Member, TenantRoleValue } from '@/Types/tenant/member';

export function useMembers() {
    function changeRole(member: Member, role: TenantRoleValue): void {
        router.put(route(ROUTE_NAMES_TENANT.MEMBERS.UPDATE, member.id), { role }, { preserveScroll: true });
    }

    function deactivate(member: Member): void {
        if (!confirm(MESSAGES.COMMON.CONFIRM_DEACTIVATE_MEMBER)) {
            return;
        }

        router.delete(route(ROUTE_NAMES_TENANT.MEMBERS.DESTROY, member.id), { preserveScroll: true });
    }

    function revokeInvitation(invitation: Invitation): void {
        if (!confirm(MESSAGES.COMMON.CONFIRM_REVOKE_INVITATION)) {
            return;
        }

        router.delete(route(ROUTE_NAMES_TENANT.INVITATIONS.DESTROY, invitation.id), { preserveScroll: true });
    }

    return { changeRole, deactivate, revokeInvitation };
}

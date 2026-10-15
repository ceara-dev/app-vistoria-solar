<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import InvitationList from '@/Components/tenant/members/InvitationList.vue';
import InviteMemberForm from '@/Components/tenant/members/InviteMemberForm.vue';
import MemberTable from '@/Components/tenant/members/MemberTable.vue';
import { PERMISSIONS } from '@/Constants';
import { useAbilities } from '@/Composables/shared/useAbilities';
import { useInviteMemberForm } from '@/Composables/tenant/useInviteMemberForm';
import { useMembers } from '@/Composables/tenant/useMembers';
import type { ResourceCollection } from '@/Types/shared';
import type { Invitation, Member, RoleOption } from '@/Types/tenant/member';

defineProps<{ members: ResourceCollection<Member>; invitations: ResourceCollection<Invitation>; roles: RoleOption[] }>();

const { can } = useAbilities();
const { changeRole, deactivate, revokeInvitation } = useMembers();
const { form, validateField, submit } = useInviteMemberForm();
</script>

<template>
    <Head title="Membros" />

    <TenantLayout title="Membros" subtitle="Quem tem acesso a esta empresa e com qual papel.">
        <div v-if="$page.props.errors.member" role="alert" class="alert alert-error alert-soft mb-6 text-sm">
            <Icon icon="mdi:alert-circle-outline" class="size-5" />
            {{ $page.props.errors.member }}
        </div>

        <section v-if="can(PERMISSIONS.MEMBERS_MANAGE)" class="mb-8">
            <h2 class="mb-3 text-lg font-semibold text-ink">Convidar pessoa</h2>
            <InviteMemberForm :form="form" :roles="roles" @submit="submit" @validate="validateField" />
        </section>

        <MemberTable
            :members="members.data"
            :roles="roles"
            :can-manage="can(PERMISSIONS.MEMBERS_MANAGE)"
            @change-role="changeRole"
            @deactivate="deactivate"
        />

        <section v-if="invitations.data.length > 0" class="mt-8">
            <h2 class="mb-3 text-lg font-semibold text-ink">Convites pendentes</h2>
            <InvitationList :invitations="invitations.data" :can-manage="can(PERMISSIONS.MEMBERS_MANAGE)" @revoke="revokeInvitation" />
        </section>
    </TenantLayout>
</template>

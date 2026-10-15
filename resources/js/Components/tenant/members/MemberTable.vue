<script setup lang="ts">
import { Icon } from '@iconify/vue';
import type { Member, RoleOption, TenantRoleValue } from '@/Types/tenant/member';

defineProps<{ members: Member[]; roles: RoleOption[]; canManage: boolean }>();

defineEmits<{
    changeRole: [member: Member, role: TenantRoleValue];
    deactivate: [member: Member];
}>();
</script>

<template>
    <div class="overflow-x-auto rounded-box bg-base-100 shadow-sm">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Papel</th>
                    <th>Situação</th>
                    <th v-if="canManage"><span class="sr-only">Ações</span></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="member in members" :key="member.id" :class="{ 'opacity-60': !member.is_active }">
                    <td>
                        <p class="font-medium text-ink">{{ member.name }}<span v-if="member.is_current_user" class="text-ink-subtle"> (você)</span></p>
                        <p class="text-xs text-ink-muted">{{ member.email }}</p>
                    </td>
                    <td>
                        <select
                            v-if="canManage && member.is_active && !member.is_current_user"
                            class="select select-sm rounded-lg"
                            :value="member.role"
                            :aria-label="`Papel de ${member.name}`"
                            @change="$emit('changeRole', member, ($event.target as HTMLSelectElement).value as TenantRoleValue)"
                        >
                            <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                        </select>
                        <span v-else>{{ member.role_label ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="badge badge-soft" :class="member.is_active ? 'badge-success' : 'badge-neutral'">
                            {{ member.is_active ? 'Ativo' : 'Sem acesso' }}
                        </span>
                    </td>
                    <td v-if="canManage" class="text-right">
                        <button
                            v-if="member.is_active && !member.is_current_user"
                            type="button"
                            class="btn btn-ghost btn-sm text-error"
                            @click="$emit('deactivate', member)"
                        >
                            <Icon icon="mdi:account-off-outline" class="size-4" />
                            Remover acesso
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

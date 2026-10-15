<script setup lang="ts">
import { Icon } from '@iconify/vue';
import type { Invitation } from '@/Types/tenant/member';
import { formatDate } from '@/Utils';

defineProps<{ invitations: Invitation[]; canManage: boolean }>();

defineEmits<{ revoke: [invitation: Invitation] }>();
</script>

<template>
    <ul class="divide-y divide-base-200 rounded-box bg-base-100 shadow-sm">
        <li v-for="invitation in invitations" :key="invitation.id" class="flex items-center justify-between gap-4 p-4">
            <div>
                <p class="font-medium text-ink">{{ invitation.email }}</p>
                <p class="text-xs text-ink-muted">{{ invitation.role_label }} · expira em {{ formatDate(invitation.expires_at) }}</p>
            </div>
            <button v-if="canManage" type="button" class="btn btn-ghost btn-sm" @click="$emit('revoke', invitation)">
                <Icon icon="mdi:close" class="size-4" />
                Cancelar
            </button>
        </li>
    </ul>
</template>

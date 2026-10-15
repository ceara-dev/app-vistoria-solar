<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { TENANT_STATUS_BADGES } from '@/Constants';
import { useCentralTenantActions } from '@/Composables/central/useCentralTenantActions';
import type { CentralTenant } from '@/Types/central/tenant';
import { formatCnpj, formatDate } from '@/Utils';

defineProps<{ tenants: CentralTenant[] }>();

const { suspend, activate, extendTrial } = useCentralTenantActions();
</script>

<template>
    <div class="overflow-x-auto rounded-box bg-base-100 shadow-sm">
        <table class="table">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>Cliente B2B</th>
                    <th>Plano</th>
                    <th class="text-right">Membros</th>
                    <th class="text-right">Clientes</th>
                    <th>Situação</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="tenant in tenants" :key="tenant.id" class="hover:bg-base-200">
                    <td>
                        <p class="font-medium text-ink">{{ tenant.name }}</p>
                        <p class="text-xs text-ink-muted tabular-nums">{{ tenant.document ? formatCnpj(tenant.document) : '—' }}</p>
                    </td>
                    <td>{{ tenant.b2b_client?.name ?? '—' }}</td>
                    <td>{{ tenant.plan?.name ?? '—' }}</td>
                    <td class="text-right tabular-nums">{{ tenant.members_count }}</td>
                    <td class="text-right tabular-nums">{{ tenant.customers_count }}</td>
                    <td>
                        <span class="badge badge-soft" :class="TENANT_STATUS_BADGES[tenant.status]">{{ tenant.status_label }}</span>
                        <p v-if="tenant.status === 'trial' && tenant.trial_ends_at" class="mt-1 text-xs text-ink-muted">
                            até {{ formatDate(tenant.trial_ends_at) }}
                        </p>
                    </td>
                    <td>
                        <div class="flex justify-end gap-1">
                            <button
                                v-if="tenant.status === 'trial'"
                                type="button"
                                class="btn btn-ghost btn-xs rounded-full"
                                @click="extendTrial(tenant)"
                            >
                                <Icon icon="mdi:calendar-plus" class="size-4" />
                                Estender teste
                            </button>
                            <button
                                v-if="tenant.status === 'trial' || tenant.status === 'active'"
                                type="button"
                                class="btn btn-ghost btn-xs rounded-full"
                                @click="suspend(tenant)"
                            >
                                <Icon icon="mdi:pause-circle-outline" class="size-4" />
                                Suspender
                            </button>
                            <button
                                v-if="tenant.status === 'suspended'"
                                type="button"
                                class="btn btn-ghost btn-xs rounded-full"
                                @click="activate(tenant)"
                            >
                                <Icon icon="mdi:play-circle-outline" class="size-4" />
                                Reativar
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="tenants.length === 0">
                    <td colspan="7" class="py-10 text-center text-ink-muted">Nenhuma empresa encontrada.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

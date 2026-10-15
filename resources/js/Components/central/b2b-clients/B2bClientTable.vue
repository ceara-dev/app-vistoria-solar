<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { ROUTE_NAMES_CENTRAL } from '@/Constants';
import { useCentralB2bClientActions } from '@/Composables/central/useCentralB2bClientActions';
import type { B2bClient } from '@/Types/central/b2bClient';
import { formatCnpj } from '@/Utils';

defineProps<{ clients: B2bClient[] }>();

const { destroy } = useCentralB2bClientActions();
</script>

<template>
    <div class="overflow-x-auto rounded-box bg-base-100 shadow-sm">
        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>CNPJ</th>
                    <th>Contato</th>
                    <th class="text-right">Empresas</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="client in clients" :key="client.id" class="hover:bg-base-200">
                    <td>
                        <p class="font-medium text-ink">{{ client.trade_name ?? client.legal_name }}</p>
                        <p class="text-xs text-ink-muted">{{ client.trade_name ? client.legal_name : 'Sem nome fantasia' }}</p>
                    </td>
                    <td class="tabular-nums">{{ client.document ? formatCnpj(client.document) : '—' }}</td>
                    <td>
                        <p>{{ client.contact_name ?? '—' }}</p>
                        <p v-if="client.contact_email" class="text-xs text-ink-muted">{{ client.contact_email }}</p>
                    </td>
                    <td class="text-right tabular-nums">{{ client.tenants_count ?? 0 }}</td>
                    <td>
                        <div class="flex justify-end gap-1">
                            <Link
                                :href="route(ROUTE_NAMES_CENTRAL.B2B_CLIENTS.EDIT, client.id)"
                                class="btn btn-ghost btn-xs rounded-full"
                            >
                                <Icon icon="mdi:pencil-outline" class="size-4" />
                                Editar
                            </Link>
                            <button type="button" class="btn btn-ghost btn-xs rounded-full text-error" @click="destroy(client)">
                                <Icon icon="mdi:trash-can-outline" class="size-4" />
                                Excluir
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="clients.length === 0">
                    <td colspan="5" class="py-10 text-center text-ink-muted">Nenhum cliente B2B encontrado.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

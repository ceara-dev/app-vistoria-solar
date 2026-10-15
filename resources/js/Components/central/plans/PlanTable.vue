<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { ROUTE_NAMES_CENTRAL } from '@/Constants';
import type { Plan } from '@/Types/central/plan';
import { formatCurrency } from '@/Utils';

defineProps<{ plans: Plan[] }>();

function formatStorage(megabytes: number): string {
    return megabytes >= 1024 ? `${Number((megabytes / 1024).toFixed(1))} GB` : `${megabytes} MB`;
}

function limitsSummary(plan: Plan): string {
    if (plan.limits === null) {
        return 'Ilimitado';
    }

    const parts: string[] = [];

    if (plan.limits.users) {
        parts.push(`${plan.limits.users} usuários`);
    }

    if (plan.limits.trips_per_month) {
        parts.push(`${plan.limits.trips_per_month} vistorias/mês`);
    }

    if (plan.limits.storage_mb) {
        parts.push(formatStorage(plan.limits.storage_mb));
    }

    return parts.length === 0 ? 'Ilimitado' : parts.join(' · ');
}
</script>

<template>
    <div class="overflow-x-auto rounded-box bg-base-100 shadow-sm">
        <table class="table">
            <thead>
                <tr>
                    <th>Plano</th>
                    <th>Preço mensal</th>
                    <th>Limites</th>
                    <th class="text-right">Empresas</th>
                    <th>Situação</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="plan in plans" :key="plan.id" class="hover:bg-base-200">
                    <td class="font-medium text-ink">{{ plan.name }}</td>
                    <td class="tabular-nums">{{ plan.price_cents === 0 ? 'Grátis' : formatCurrency(plan.price_cents) }}</td>
                    <td class="text-sm text-ink-muted">{{ limitsSummary(plan) }}</td>
                    <td class="text-right tabular-nums">{{ plan.tenants_count ?? 0 }}</td>
                    <td>
                        <span class="badge badge-soft" :class="plan.is_active ? 'badge-success' : 'badge-neutral'">
                            {{ plan.is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td>
                        <div class="flex justify-end">
                            <Link :href="route(ROUTE_NAMES_CENTRAL.PLANS.EDIT, plan.id)" class="btn btn-ghost btn-xs rounded-full">
                                <Icon icon="mdi:pencil-outline" class="size-4" />
                                Editar
                            </Link>
                        </div>
                    </td>
                </tr>
                <tr v-if="plans.length === 0">
                    <td colspan="6" class="py-10 text-center text-ink-muted">Nenhum plano cadastrado.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

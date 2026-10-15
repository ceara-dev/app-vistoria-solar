<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import CentralLayout from '@/Layouts/CentralLayout.vue';

interface CentralStats {
    tenants: {
        total: number;
        trial: number;
        active: number;
        suspended: number;
        canceled: number;
    };
    plans: number;
}

defineProps<{ stats: CentralStats }>();

const cards: { key: keyof CentralStats['tenants'] | 'plans'; label: string; icon: string }[] = [
    { key: 'total', label: 'Empresas na plataforma', icon: 'mdi:domain' },
    { key: 'trial', label: 'Em teste', icon: 'mdi:clock-outline' },
    { key: 'active', label: 'Ativas', icon: 'mdi:check-circle-outline' },
    { key: 'suspended', label: 'Suspensas', icon: 'mdi:pause-circle-outline' },
    { key: 'canceled', label: 'Canceladas', icon: 'mdi:close-circle-outline' },
    { key: 'plans', label: 'Planos', icon: 'mdi:tag-outline' },
];
</script>

<template>
    <Head title="Central" />

    <CentralLayout title="Central" subtitle="Acompanhamento das empresas da plataforma.">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="card in cards" :key="card.key" class="rounded-box bg-base-100 p-5 shadow-sm">
                <p class="flex items-center gap-2 text-sm font-medium text-ink-muted">
                    <Icon :icon="card.icon" class="size-5 text-primary" />
                    {{ card.label }}
                </p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-ink">
                    {{ card.key === 'plans' ? stats.plans : stats.tenants[card.key] }}
                </p>
            </div>
        </div>
    </CentralLayout>
</template>

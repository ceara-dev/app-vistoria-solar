<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import CentralLayout from '@/Layouts/CentralLayout.vue';
import PlanTable from '@/Components/central/plans/PlanTable.vue';
import { ROUTE_NAMES_CENTRAL } from '@/Constants';
import type { Plan } from '@/Types/central/plan';
import type { ResourceCollection } from '@/Types/shared';

defineProps<{ plans: ResourceCollection<Plan> }>();
</script>

<template>
    <Head title="Planos" />

    <CentralLayout title="Planos" subtitle="Os planos comerciais da plataforma. Os limites ainda não bloqueiam o uso.">
        <div class="mb-6 flex justify-end">
            <Link :href="route(ROUTE_NAMES_CENTRAL.PLANS.CREATE)" class="btn btn-primary rounded-full">
                <Icon icon="mdi:plus" class="size-5" />
                Novo plano
            </Link>
        </div>

        <PlanTable :plans="plans.data" />
    </CentralLayout>
</template>

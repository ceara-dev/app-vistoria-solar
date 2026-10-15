<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CentralLayout from '@/Layouts/CentralLayout.vue';
import PlanForm from '@/Components/central/plans/PlanForm.vue';
import { useCentralPlanForm } from '@/Composables/central/useCentralPlanForm';
import type { Plan } from '@/Types/central/plan';
import type { ResourceItem } from '@/Types/shared';

const props = defineProps<{ plan: ResourceItem<Plan> }>();

const { form, validateField, submit } = useCentralPlanForm(props.plan.data);
</script>

<template>
    <Head :title="`Editar ${plan.data.name}`" />

    <CentralLayout :title="`Editar ${plan.data.name}`">
        <PlanForm :form="form" submit-label="Salvar plano" @submit="submit" @validate="validateField" />
    </CentralLayout>
</template>

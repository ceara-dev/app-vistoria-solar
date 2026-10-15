<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CentralLayout from '@/Layouts/CentralLayout.vue';
import PlanForm from '@/Components/central/plans/PlanForm.vue';
import { useCentralPlanForm } from '@/Composables/central/useCentralPlanForm';

const { form, validateField, submit } = useCentralPlanForm();
</script>

<template>
    <Head title="Novo plano" />

    <CentralLayout title="Novo plano">
        <PlanForm :form="form" submit-label="Criar plano" @submit="submit" @validate="validateField" />
    </CentralLayout>
</template>

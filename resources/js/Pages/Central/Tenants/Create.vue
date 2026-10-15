<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CentralLayout from '@/Layouts/CentralLayout.vue';
import TenantForm from '@/Components/central/tenants/TenantForm.vue';
import { useCentralTenantForm } from '@/Composables/central/useCentralTenantForm';
import type { TenantFormOptions } from '@/Types/central/tenant';

const props = defineProps<TenantFormOptions>();

const { form, validateField, submit } = useCentralTenantForm();
</script>

<template>
    <Head title="Nova empresa" />

    <CentralLayout title="Nova empresa" subtitle="A empresa nasce em período de teste de 14 dias.">
        <TenantForm :form="form" :plans="props.plans" :b2b-clients="props.b2bClients" @submit="submit" @validate="validateField" />
    </CentralLayout>
</template>

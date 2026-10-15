<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CentralLayout from '@/Layouts/CentralLayout.vue';
import B2bClientForm from '@/Components/central/b2b-clients/B2bClientForm.vue';
import { useCentralB2bClientForm } from '@/Composables/central/useCentralB2bClientForm';

const { form, validateField, submit } = useCentralB2bClientForm();
</script>

<template>
    <Head title="Novo cliente B2B" />

    <CentralLayout title="Novo cliente B2B">
        <B2bClientForm :form="form" submit-label="Criar cliente" @submit="submit" @validate="validateField" />
    </CentralLayout>
</template>

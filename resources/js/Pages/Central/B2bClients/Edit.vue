<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CentralLayout from '@/Layouts/CentralLayout.vue';
import B2bClientForm from '@/Components/central/b2b-clients/B2bClientForm.vue';
import { useCentralB2bClientForm } from '@/Composables/central/useCentralB2bClientForm';
import type { B2bClient } from '@/Types/central/b2bClient';
import type { ResourceItem } from '@/Types/shared';

const props = defineProps<{ client: ResourceItem<B2bClient> }>();

const { form, validateField, submit } = useCentralB2bClientForm(props.client.data);
</script>

<template>
    <Head :title="`Editar ${client.data.trade_name ?? client.data.legal_name}`" />

    <CentralLayout :title="`Editar ${client.data.trade_name ?? client.data.legal_name}`">
        <B2bClientForm :form="form" submit-label="Salvar cliente" @submit="submit" @validate="validateField" />
    </CentralLayout>
</template>

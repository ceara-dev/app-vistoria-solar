<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import CentralLayout from '@/Layouts/CentralLayout.vue';
import AppPagination from '@/Components/common/AppPagination.vue';
import B2bClientTable from '@/Components/central/b2b-clients/B2bClientTable.vue';
import { ROUTE_NAMES_CENTRAL } from '@/Constants';
import { useCentralB2bClientSearch } from '@/Composables/central/useCentralB2bClientSearch';
import type { B2bClient, B2bClientFilters } from '@/Types/central/b2bClient';
import type { Paginated } from '@/Types/shared';

const props = defineProps<{ clients: Paginated<B2bClient>; filters: B2bClientFilters }>();

const { search } = useCentralB2bClientSearch(props.filters.search);
</script>

<template>
    <Head title="Clientes B2B" />

    <CentralLayout title="Clientes B2B" subtitle="Empresas que contratam a plataforma, vinculadas às empresas do sistema.">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <label class="input h-11 w-full max-w-md rounded-full">
                <Icon icon="mdi:magnify" class="size-5 opacity-50" />
                <input v-model="search" type="search" class="grow" placeholder="Buscar por nome ou CNPJ" aria-label="Buscar clientes B2B" />
            </label>
            <Link :href="route(ROUTE_NAMES_CENTRAL.B2B_CLIENTS.CREATE)" class="btn btn-primary rounded-full">
                <Icon icon="mdi:plus" class="size-5" />
                Novo cliente B2B
            </Link>
        </div>

        <B2bClientTable :clients="clients.data" />

        <AppPagination :meta="clients.meta" class="mt-4" />
    </CentralLayout>
</template>

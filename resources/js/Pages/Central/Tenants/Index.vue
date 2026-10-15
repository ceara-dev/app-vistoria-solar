<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import CentralLayout from '@/Layouts/CentralLayout.vue';
import AppPagination from '@/Components/common/AppPagination.vue';
import AppSelect from '@/Components/common/AppSelect.vue';
import TenantTable from '@/Components/central/tenants/TenantTable.vue';
import { ROUTE_NAMES_CENTRAL } from '@/Constants';
import { useCentralTenantSearch } from '@/Composables/central/useCentralTenantSearch';
import type { CentralTenant, CentralTenantFilters } from '@/Types/central/tenant';
import type { Paginated, SelectOption } from '@/Types/shared';

const props = defineProps<{ tenants: Paginated<CentralTenant>; filters: CentralTenantFilters; statuses: SelectOption[] }>();

const { search, status } = useCentralTenantSearch(props.filters.search, props.filters.status);

const statusOptions = computed<SelectOption<string>[]>(() => [
    { value: '', label: 'Todas as situações' },
    ...props.statuses,
]);
</script>

<template>
    <Head title="Empresas" />

    <CentralLayout title="Empresas" subtitle="Todas as empresas da plataforma, com plano e uso.">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-1 flex-wrap items-center gap-3">
                <label class="input h-11 w-full max-w-md rounded-full">
                    <Icon icon="mdi:magnify" class="size-5 opacity-50" />
                    <input
                        v-model="search"
                        type="search"
                        class="grow"
                        placeholder="Buscar por nome ou CNPJ"
                        aria-label="Buscar empresas"
                    />
                </label>
                <AppSelect v-model="status" :options="statusOptions" class="h-11 w-56 rounded-full" aria-label="Filtrar por situação" />
            </div>
            <Link :href="route(ROUTE_NAMES_CENTRAL.TENANTS.CREATE)" class="btn btn-primary rounded-full">
                <Icon icon="mdi:plus" class="size-5" />
                Nova empresa
            </Link>
        </div>

        <TenantTable :tenants="tenants.data" />

        <AppPagination :meta="tenants.meta" class="mt-4" />
    </CentralLayout>
</template>

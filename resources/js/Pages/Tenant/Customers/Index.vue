<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import AppPagination from '@/Components/common/AppPagination.vue';
import CustomerTable from '@/Components/tenant/customers/CustomerTable.vue';
import { PERMISSIONS, ROUTE_NAMES_TENANT } from '@/Constants';
import { useAbilities } from '@/Composables/shared/useAbilities';
import { useCustomerSearch } from '@/Composables/tenant/useCustomerSearch';
import type { Paginated } from '@/Types/shared';
import type { Customer } from '@/Types/tenant/customer';

const props = defineProps<{ customers: Paginated<Customer>; filters: { search: string | null } }>();

const { can } = useAbilities();
const { search } = useCustomerSearch(props.filters.search);
</script>

<template>
    <Head title="Clientes" />

    <TenantLayout title="Clientes" subtitle="Quem tem sistemas fotovoltaicos acompanhados pela empresa.">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <label class="input h-11 w-full max-w-md rounded-full">
                <Icon icon="mdi:magnify" class="size-5 opacity-50" />
                <input v-model="search" type="search" class="grow" placeholder="Buscar por nome, e-mail ou documento" aria-label="Buscar clientes" />
            </label>
            <Link v-if="can(PERMISSIONS.CUSTOMERS_MANAGE)" :href="route(ROUTE_NAMES_TENANT.CUSTOMERS.CREATE)" class="btn btn-primary rounded-full">
                <Icon icon="mdi:plus" class="size-5" />
                Novo cliente
            </Link>
        </div>

        <CustomerTable :customers="customers.data" />

        <AppPagination :meta="customers.meta" class="mt-4" />
    </TenantLayout>
</template>

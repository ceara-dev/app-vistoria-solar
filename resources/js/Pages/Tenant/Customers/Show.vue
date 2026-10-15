<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import CustomerDetails from '@/Components/tenant/customers/CustomerDetails.vue';
import { PERMISSIONS, ROUTE_NAMES_TENANT } from '@/Constants';
import { useAbilities } from '@/Composables/shared/useAbilities';
import { useCustomerActions } from '@/Composables/tenant/useCustomerActions';
import type { ResourceItem } from '@/Types/shared';
import type { Customer } from '@/Types/tenant/customer';

defineProps<{ customer: ResourceItem<Customer> }>();

const { can } = useAbilities();
const { deleteCustomer } = useCustomerActions();
</script>

<template>
    <Head :title="customer.data.name" />

    <TenantLayout :title="customer.data.name" :subtitle="customer.data.type_label">
        <div class="mb-6 flex flex-wrap gap-2">
            <Link :href="route(ROUTE_NAMES_TENANT.CUSTOMERS.INDEX)" class="btn btn-ghost btn-sm rounded-full">
                <Icon icon="mdi:arrow-left" class="size-4" />
                Clientes
            </Link>
            <template v-if="can(PERMISSIONS.CUSTOMERS_MANAGE)">
                <Link :href="route(ROUTE_NAMES_TENANT.CUSTOMERS.EDIT, customer.data.id)" class="btn btn-outline btn-primary btn-sm rounded-full">
                    <Icon icon="mdi:pencil-outline" class="size-4" />
                    Editar
                </Link>
                <button type="button" class="btn btn-ghost btn-sm rounded-full text-error" @click="deleteCustomer(customer.data)">
                    <Icon icon="mdi:trash-can-outline" class="size-4" />
                    Excluir
                </button>
            </template>
        </div>

        <CustomerDetails :customer="customer.data" />
    </TenantLayout>
</template>

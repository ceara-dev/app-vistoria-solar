<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import CustomerForm from '@/Components/tenant/customers/CustomerForm.vue';
import { ROUTE_NAMES_TENANT } from '@/Constants';
import { useCustomerForm } from '@/Composables/tenant/useCustomerForm';
import type { ResourceItem } from '@/Types/shared';
import type { Customer, CustomerFormOptions } from '@/Types/tenant/customer';

const props = defineProps<CustomerFormOptions & { customer: ResourceItem<Customer> }>();

const { form, errorAt, validateField, addAddress, removeAddress, addContact, removeContact, submit } = useCustomerForm(props.customer.data);
</script>

<template>
    <Head :title="`Editar ${customer.data.name}`" />

    <TenantLayout :title="`Editar ${customer.data.name}`">
        <CustomerForm
            :form="form"
            :options="{ types, states }"
            :error-at="errorAt"
            :cancel-url="route(ROUTE_NAMES_TENANT.CUSTOMERS.SHOW, customer.data.id)"
            @submit="submit"
            @validate="validateField"
            @add-address="addAddress"
            @remove-address="removeAddress"
            @add-contact="addContact"
            @remove-contact="removeContact"
        />
    </TenantLayout>
</template>

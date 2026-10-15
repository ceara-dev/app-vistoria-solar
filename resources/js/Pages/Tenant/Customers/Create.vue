<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import CustomerForm from '@/Components/tenant/customers/CustomerForm.vue';
import { ROUTE_NAMES_TENANT } from '@/Constants';
import { useCustomerForm } from '@/Composables/tenant/useCustomerForm';
import type { CustomerFormOptions } from '@/Types/tenant/customer';

const props = defineProps<CustomerFormOptions>();

const { form, errorAt, validateField, addAddress, removeAddress, addContact, removeContact, submit } = useCustomerForm();
</script>

<template>
    <Head title="Novo cliente" />

    <TenantLayout title="Novo cliente">
        <CustomerForm
            :form="form"
            :options="props"
            :error-at="errorAt"
            :cancel-url="route(ROUTE_NAMES_TENANT.CUSTOMERS.INDEX)"
            @submit="submit"
            @validate="validateField"
            @add-address="addAddress"
            @remove-address="removeAddress"
            @add-contact="addContact"
            @remove-contact="removeContact"
        />
    </TenantLayout>
</template>

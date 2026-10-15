<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ROUTE_NAMES_TENANT } from '@/Constants';
import type { Customer } from '@/Types/tenant/customer';
import { formatDocument, formatPhone } from '@/Utils';

defineProps<{ customers: Customer[] }>();
</script>

<template>
    <div class="overflow-x-auto rounded-box bg-base-100 shadow-sm">
        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Documento</th>
                    <th>Contato</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="customer in customers" :key="customer.id" class="hover:bg-base-200">
                    <td>
                        <Link :href="route(ROUTE_NAMES_TENANT.CUSTOMERS.SHOW, customer.id)" class="font-medium text-ink hover:text-primary">
                            {{ customer.name }}
                        </Link>
                        <p class="text-xs text-ink-muted">{{ customer.trade_name ?? customer.type_label }}</p>
                    </td>
                    <td class="tabular-nums">{{ customer.document ? formatDocument(customer.type, customer.document) : '—' }}</td>
                    <td>
                        <p>{{ customer.email ?? '—' }}</p>
                        <p v-if="customer.phone" class="text-xs text-ink-muted tabular-nums">{{ formatPhone(customer.phone) }}</p>
                    </td>
                </tr>
                <tr v-if="customers.length === 0">
                    <td colspan="3" class="py-10 text-center text-ink-muted">Nenhum cliente encontrado.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup lang="ts">
import type { Customer } from '@/Types/tenant/customer';
import { formatDocument, formatPhone, formatZipCode } from '@/Utils';

defineProps<{ customer: Customer }>();
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-box bg-base-100 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-ink">Dados</h2>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-ink-muted">Tipo</dt><dd>{{ customer.type_label }}</dd></div>
                <div v-if="customer.trade_name"><dt class="text-ink-muted">Nome fantasia</dt><dd>{{ customer.trade_name }}</dd></div>
                <div><dt class="text-ink-muted">{{ customer.type === 'PJ' ? 'CNPJ' : 'CPF' }}</dt><dd class="tabular-nums">{{ customer.document ? formatDocument(customer.type, customer.document) : '—' }}</dd></div>
                <div><dt class="text-ink-muted">E-mail</dt><dd>{{ customer.email ?? '—' }}</dd></div>
                <div><dt class="text-ink-muted">Telefone</dt><dd class="tabular-nums">{{ customer.phone ? formatPhone(customer.phone) : '—' }}</dd></div>
                <div v-if="customer.notes"><dt class="text-ink-muted">Observações</dt><dd class="whitespace-pre-line">{{ customer.notes }}</dd></div>
            </dl>
        </section>

        <section class="rounded-box bg-base-100 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-ink">Endereços</h2>
            <ul v-if="customer.addresses?.length" class="space-y-4 text-sm">
                <li v-for="address in customer.addresses" :key="address.id">
                    <p class="font-medium text-ink">{{ address.label ?? 'Endereço' }}</p>
                    <p>{{ address.street }}, {{ address.number }}<span v-if="address.complement"> - {{ address.complement }}</span></p>
                    <p class="text-ink-muted">{{ address.district }} · {{ address.city }}/{{ address.state }} · {{ formatZipCode(address.zip_code) }}</p>
                </li>
            </ul>
            <p v-else class="text-sm text-ink-muted">Nenhum endereço cadastrado.</p>
        </section>

        <section class="rounded-box bg-base-100 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-ink">Contatos</h2>
            <ul v-if="customer.contacts?.length" class="space-y-4 text-sm">
                <li v-for="contact in customer.contacts" :key="contact.id">
                    <p class="font-medium text-ink">{{ contact.name }}<span v-if="contact.position" class="font-normal text-ink-muted"> · {{ contact.position }}</span></p>
                    <p v-if="contact.email">{{ contact.email }}</p>
                    <p v-if="contact.phone" class="tabular-nums">{{ formatPhone(contact.phone) }}</p>
                </li>
            </ul>
            <p v-else class="text-sm text-ink-muted">Nenhum contato cadastrado.</p>
        </section>
    </div>
</template>

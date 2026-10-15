<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppSelect from '@/Components/common/AppSelect.vue';
import AppTextarea from '@/Components/common/AppTextarea.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import CustomerAddressFields from '@/Components/tenant/customers/CustomerAddressFields.vue';
import CustomerContactFields from '@/Components/tenant/customers/CustomerContactFields.vue';
import { FIELD_LIMITS } from '@/Constants';
import type { CustomerFormOptions, CustomerPayload } from '@/Types/tenant/customer';

defineProps<{
    form: InertiaForm<CustomerPayload>;
    options: CustomerFormOptions;
    errorAt: (path: string) => string | undefined;
    cancelUrl: string;
}>();

defineEmits<{
    submit: [];
    validate: [path: string];
    addAddress: [];
    removeAddress: [index: number];
    addContact: [];
    removeContact: [index: number];
}>();
</script>

<template>
    <form class="space-y-8" novalidate @submit.prevent="$emit('submit')">
        <section class="rounded-box bg-base-100 p-6 shadow-sm">
            <h2 class="mb-2 text-lg font-semibold text-ink">Dados do cliente</h2>
            <div class="grid gap-x-4 sm:grid-cols-2">
                <AppFormField label="Tipo" :error="form.errors.type">
                    <AppSelect v-model="form.type" :options="options.types" :error="form.errors.type" @change="$emit('validate', 'document')" />
                </AppFormField>
                <AppFormField :label="form.type === 'PJ' ? 'CNPJ' : 'CPF'" :error="form.errors.document">
                    <AppTextInput v-model="form.document" :error="form.errors.document" inputmode="numeric" @blur="$emit('validate', 'document')" />
                </AppFormField>
                <AppFormField :label="form.type === 'PJ' ? 'Razão social' : 'Nome completo'" :error="form.errors.name">
                    <AppTextInput v-model="form.name" :error="form.errors.name" :maxlength="FIELD_LIMITS.CUSTOMER_NAME_MAX" required @blur="$emit('validate', 'name')" />
                </AppFormField>
                <AppFormField v-if="form.type === 'PJ'" label="Nome fantasia" :error="form.errors.trade_name">
                    <AppTextInput v-model="form.trade_name" :error="form.errors.trade_name" :maxlength="FIELD_LIMITS.TRADE_NAME_MAX" @blur="$emit('validate', 'trade_name')" />
                </AppFormField>
                <AppFormField label="E-mail" :error="form.errors.email">
                    <AppTextInput v-model="form.email" :error="form.errors.email" :maxlength="FIELD_LIMITS.EMAIL_MAX" type="email" @blur="$emit('validate', 'email')" />
                </AppFormField>
                <AppFormField label="Telefone" :error="form.errors.phone">
                    <AppTextInput v-model="form.phone" :error="form.errors.phone" inputmode="tel" placeholder="(00) 00000-0000" @blur="$emit('validate', 'phone')" />
                </AppFormField>
            </div>
            <AppFormField label="Observações" :error="form.errors.notes">
                <AppTextarea v-model="form.notes" :error="form.errors.notes" :maxlength="FIELD_LIMITS.NOTES_MAX" @blur="$emit('validate', 'notes')" />
            </AppFormField>
        </section>

        <section class="space-y-4 rounded-box bg-base-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-ink">Endereços</h2>
            <CustomerAddressFields
                v-for="(address, index) in form.addresses"
                :key="address.id ?? `novo-${index}`"
                :address="address"
                :index="index"
                :states="options.states"
                :error-at="errorAt"
                @validate="$emit('validate', $event)"
                @remove="$emit('removeAddress', index)"
            />
            <button type="button" class="btn btn-outline btn-sm rounded-full" @click="$emit('addAddress')">
                <Icon icon="mdi:plus" class="size-4" />
                Adicionar endereço
            </button>
        </section>

        <section class="space-y-4 rounded-box bg-base-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-ink">Contatos</h2>
            <CustomerContactFields
                v-for="(contact, index) in form.contacts"
                :key="contact.id ?? `novo-${index}`"
                :contact="contact"
                :index="index"
                :error-at="errorAt"
                @validate="$emit('validate', $event)"
                @remove="$emit('removeContact', index)"
            />
            <button type="button" class="btn btn-outline btn-sm rounded-full" @click="$emit('addContact')">
                <Icon icon="mdi:plus" class="size-4" />
                Adicionar contato
            </button>
        </section>

        <div class="flex justify-end gap-3">
            <Link :href="cancelUrl" class="btn btn-ghost rounded-full">Cancelar</Link>
            <button type="submit" class="btn btn-primary rounded-full px-8" :disabled="form.processing">
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                Salvar
            </button>
        </div>
    </form>
</template>

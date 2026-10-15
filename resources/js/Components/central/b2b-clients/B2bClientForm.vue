<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import { FIELD_LIMITS, ROUTE_NAMES_CENTRAL } from '@/Constants';
import type { B2bClientPayload } from '@/Types/central/b2bClient';

defineProps<{ form: InertiaForm<B2bClientPayload>; submitLabel: string }>();

defineEmits<{
    submit: [];
    validate: [path: string];
}>();
</script>

<template>
    <form class="max-w-2xl space-y-8" novalidate @submit.prevent="$emit('submit')">
        <section class="space-y-4 rounded-box bg-base-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-ink">Dados do cliente</h2>

            <AppFormField label="Razão social" :error="form.errors.legal_name">
                <AppTextInput
                    v-model="form.legal_name"
                    :error="form.errors.legal_name"
                    :maxlength="FIELD_LIMITS.NAME_MAX"
                    required
                    @blur="$emit('validate', 'legal_name')"
                />
            </AppFormField>

            <AppFormField label="Nome fantasia" :error="form.errors.trade_name">
                <AppTextInput
                    v-model="form.trade_name"
                    :error="form.errors.trade_name"
                    :maxlength="FIELD_LIMITS.TRADE_NAME_MAX"
                    @blur="$emit('validate', 'trade_name')"
                />
            </AppFormField>

            <AppFormField label="CNPJ" :error="form.errors.document">
                <AppTextInput
                    v-model="form.document"
                    :error="form.errors.document"
                    inputmode="numeric"
                    placeholder="00.000.000/0000-00"
                    @blur="$emit('validate', 'document')"
                />
            </AppFormField>
        </section>

        <section class="space-y-4 rounded-box bg-base-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-ink">Contato</h2>

            <div class="grid gap-x-4 sm:grid-cols-2">
                <AppFormField label="Nome" :error="form.errors.contact_name">
                    <AppTextInput
                        v-model="form.contact_name"
                        :error="form.errors.contact_name"
                        :maxlength="FIELD_LIMITS.NAME_MAX"
                        @blur="$emit('validate', 'contact_name')"
                    />
                </AppFormField>
                <AppFormField label="E-mail" :error="form.errors.contact_email">
                    <AppTextInput
                        v-model="form.contact_email"
                        :error="form.errors.contact_email"
                        :maxlength="FIELD_LIMITS.EMAIL_MAX"
                        type="email"
                        @blur="$emit('validate', 'contact_email')"
                    />
                </AppFormField>
                <AppFormField label="Telefone" :error="form.errors.contact_phone">
                    <AppTextInput
                        v-model="form.contact_phone"
                        :error="form.errors.contact_phone"
                        inputmode="tel"
                        placeholder="(00) 00000-0000"
                        @blur="$emit('validate', 'contact_phone')"
                    />
                </AppFormField>
            </div>
        </section>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary rounded-full" :disabled="form.processing">
                <Icon icon="mdi:content-save-outline" class="size-5" />
                {{ submitLabel }}
            </button>
            <Link :href="route(ROUTE_NAMES_CENTRAL.B2B_CLIENTS.INDEX)" class="btn btn-ghost rounded-full">Cancelar</Link>
        </div>
    </form>
</template>

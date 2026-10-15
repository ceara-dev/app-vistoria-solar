<script setup lang="ts">
import { Icon } from '@iconify/vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import { FIELD_LIMITS } from '@/Constants';
import type { CustomerContactPayload } from '@/Types/tenant/customer';

const props = defineProps<{
    contact: CustomerContactPayload;
    index: number;
    errorAt: (path: string) => string | undefined;
}>();

const emit = defineEmits<{ validate: [path: string]; remove: [] }>();

const path = (field: keyof CustomerContactPayload): string => `contacts.${props.index}.${field}`;
const validate = (field: keyof CustomerContactPayload): void => emit('validate', path(field));
</script>

<template>
    <fieldset class="rounded-box border border-base-300 p-4">
        <div class="mb-2 flex items-center justify-between">
            <legend class="font-medium text-ink">Contato {{ index + 1 }}</legend>
            <button type="button" class="btn btn-ghost btn-sm text-error" @click="$emit('remove')">
                <Icon icon="mdi:trash-can-outline" class="size-4" />
                Remover
            </button>
        </div>

        <div class="grid gap-x-4 sm:grid-cols-2">
            <AppFormField label="Nome" :error="errorAt(path('name'))">
                <AppTextInput v-model="contact.name" :error="errorAt(path('name'))" :maxlength="FIELD_LIMITS.CONTACT_NAME_MAX" required @blur="validate('name')" />
            </AppFormField>
            <AppFormField label="Cargo ou relação" :error="errorAt(path('position'))">
                <AppTextInput v-model="contact.position" :error="errorAt(path('position'))" :maxlength="FIELD_LIMITS.CONTACT_POSITION_MAX" @blur="validate('position')" />
            </AppFormField>
            <AppFormField label="E-mail" :error="errorAt(path('email'))">
                <AppTextInput v-model="contact.email" :error="errorAt(path('email'))" :maxlength="FIELD_LIMITS.EMAIL_MAX" type="email" @blur="validate('email')" />
            </AppFormField>
            <AppFormField label="Telefone" :error="errorAt(path('phone'))">
                <AppTextInput v-model="contact.phone" :error="errorAt(path('phone'))" inputmode="tel" placeholder="(00) 00000-0000" @blur="validate('phone')" />
            </AppFormField>
        </div>
    </fieldset>
</template>

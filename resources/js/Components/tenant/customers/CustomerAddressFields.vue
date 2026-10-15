<script setup lang="ts">
import { Icon } from '@iconify/vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppSelect from '@/Components/common/AppSelect.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import { FIELD_LIMITS } from '@/Constants';
import type { SelectOption } from '@/Types/shared';
import type { CustomerAddressPayload } from '@/Types/tenant/customer';

const props = defineProps<{
    address: CustomerAddressPayload;
    index: number;
    states: SelectOption[];
    errorAt: (path: string) => string | undefined;
}>();

const emit = defineEmits<{ validate: [path: string]; remove: [] }>();

const path = (field: keyof CustomerAddressPayload): string => `addresses.${props.index}.${field}`;
const validate = (field: keyof CustomerAddressPayload): void => emit('validate', path(field));
</script>

<template>
    <fieldset class="rounded-box border border-base-300 p-4">
        <div class="mb-2 flex items-center justify-between">
            <legend class="font-medium text-ink">Endereço {{ index + 1 }}</legend>
            <button type="button" class="btn btn-ghost btn-sm text-error" @click="$emit('remove')">
                <Icon icon="mdi:trash-can-outline" class="size-4" />
                Remover
            </button>
        </div>

        <div class="grid gap-x-4 sm:grid-cols-6">
            <AppFormField label="Identificação" :error="errorAt(path('label'))" class="sm:col-span-3">
                <AppTextInput v-model="address.label" :error="errorAt(path('label'))" :maxlength="FIELD_LIMITS.ADDRESS_LABEL_MAX" placeholder="Residência, usina..." @blur="validate('label')" />
            </AppFormField>
            <AppFormField label="CEP" :error="errorAt(path('zip_code'))" class="sm:col-span-3">
                <AppTextInput v-model="address.zip_code" :error="errorAt(path('zip_code'))" inputmode="numeric" placeholder="00000-000" required @blur="validate('zip_code')" />
            </AppFormField>
            <AppFormField label="Logradouro" :error="errorAt(path('street'))" class="sm:col-span-4">
                <AppTextInput v-model="address.street" :error="errorAt(path('street'))" :maxlength="FIELD_LIMITS.STREET_MAX" required @blur="validate('street')" />
            </AppFormField>
            <AppFormField label="Número" :error="errorAt(path('number'))" class="sm:col-span-2">
                <AppTextInput v-model="address.number" :error="errorAt(path('number'))" :maxlength="FIELD_LIMITS.NUMBER_MAX" required @blur="validate('number')" />
            </AppFormField>
            <AppFormField label="Complemento" :error="errorAt(path('complement'))" class="sm:col-span-3">
                <AppTextInput v-model="address.complement" :error="errorAt(path('complement'))" :maxlength="FIELD_LIMITS.COMPLEMENT_MAX" @blur="validate('complement')" />
            </AppFormField>
            <AppFormField label="Bairro" :error="errorAt(path('district'))" class="sm:col-span-3">
                <AppTextInput v-model="address.district" :error="errorAt(path('district'))" :maxlength="FIELD_LIMITS.DISTRICT_MAX" required @blur="validate('district')" />
            </AppFormField>
            <AppFormField label="Cidade" :error="errorAt(path('city'))" class="sm:col-span-4">
                <AppTextInput v-model="address.city" :error="errorAt(path('city'))" :maxlength="FIELD_LIMITS.CITY_MAX" required @blur="validate('city')" />
            </AppFormField>
            <AppFormField label="UF" :error="errorAt(path('state'))" class="sm:col-span-2">
                <AppSelect v-model="address.state" :options="states" :error="errorAt(path('state'))" required @change="validate('state')" />
            </AppFormField>
        </div>
    </fieldset>
</template>

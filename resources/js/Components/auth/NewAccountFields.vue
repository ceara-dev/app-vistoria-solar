<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import AppPasswordInput from '@/Components/common/AppPasswordInput.vue';
import AppPasswordStrength from '@/Components/common/AppPasswordStrength.vue';
import { FIELD_LIMITS } from '@/Constants';

type NewAccountField = 'name' | 'password' | 'password_confirmation';

defineProps<{ form: InertiaForm<Record<NewAccountField, string>> }>();

defineEmits<{ validate: [field: NewAccountField] }>();
</script>

<template>
    <AppFormField label="Seu nome" :error="form.errors.name">
        <AppTextInput
            v-model="form.name"
            icon="mdi:account-outline"
            :error="form.errors.name"
            :maxlength="FIELD_LIMITS.NAME_MAX"
            autocomplete="name"
            required
            autofocus
            @blur="$emit('validate', 'name')"
        />
    </AppFormField>

    <AppFormField label="Senha" :error="form.errors.password">
        <AppPasswordInput
            v-model="form.password"
            :error="form.errors.password"
            :placeholder="`Mínimo de ${FIELD_LIMITS.PASSWORD_MIN} caracteres`"
            autocomplete="new-password"
            required
            @blur="$emit('validate', 'password')"
        />
        <AppPasswordStrength :password="form.password" />
    </AppFormField>

    <AppFormField label="Confirmar senha" :error="form.errors.password_confirmation">
        <AppPasswordInput
            v-model="form.password_confirmation"
            :error="form.errors.password_confirmation"
            placeholder="Repita a senha"
            autocomplete="new-password"
            required
            @blur="$emit('validate', 'password_confirmation')"
        />
    </AppFormField>
</template>

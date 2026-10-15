<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import AppPasswordInput from '@/Components/common/AppPasswordInput.vue';
import AppPasswordStrength from '@/Components/common/AppPasswordStrength.vue';
import { FIELD_LIMITS, ROUTE_NAMES_AUTH } from '@/Constants';
import { useResetPasswordForm } from '@/Composables/auth/useResetPasswordForm';

const props = defineProps<{ email: string; token: string }>();

const { form, validateField, submit } = useResetPasswordForm(props.token, props.email);
</script>

<template>
    <Head title="Nova senha" />

    <GuestLayout title="Crie uma nova senha" subtitle="Escolha uma senha forte, com pelo menos 8 caracteres." icon="mdi:shield-key-outline">
        <form class="space-y-4" novalidate @submit.prevent="submit">
            <AppFormField label="E-mail" :error="form.errors.email">
                <AppTextInput
                    v-model="form.email"
                    icon="mdi:email-outline"
                    :error="form.errors.email"
                    :maxlength="FIELD_LIMITS.EMAIL_MAX"
                    type="email"
                    autocomplete="username"
                    required
                    @blur="validateField('email')"
                />
            </AppFormField>

            <AppFormField label="Nova senha" :error="form.errors.password">
                <AppPasswordInput
                    v-model="form.password"
                    :error="form.errors.password"
                    :placeholder="`Mínimo de ${FIELD_LIMITS.PASSWORD_MIN} caracteres`"
                    autocomplete="new-password"
                    required
                    autofocus
                    @blur="validateField('password')"
                />
                <AppPasswordStrength :password="form.password" />
            </AppFormField>

            <AppFormField label="Confirmar nova senha" :error="form.errors.password_confirmation">
                <AppPasswordInput
                    v-model="form.password_confirmation"
                    :error="form.errors.password_confirmation"
                    placeholder="Repita a nova senha"
                    autocomplete="new-password"
                    required
                    @blur="validateField('password_confirmation')"
                />
            </AppFormField>

            <button type="submit" class="btn btn-primary btn-block mt-2 h-12 rounded-full shadow-lg shadow-brand-700/20" :disabled="form.processing">
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                Salvar nova senha
            </button>
        </form>

        <Link :href="route(ROUTE_NAMES_AUTH.LOGIN)" class="btn btn-ghost btn-block mt-6 rounded-full text-ink-muted">
            <Icon icon="mdi:arrow-left" class="size-4" />
            Voltar ao login
        </Link>
    </GuestLayout>
</template>

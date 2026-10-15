<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import AppPasswordInput from '@/Components/common/AppPasswordInput.vue';
import AppPasswordStrength from '@/Components/common/AppPasswordStrength.vue';
import { FIELD_LIMITS, ROUTE_NAMES_AUTH } from '@/Constants';
import { useRegisterForm } from '@/Composables/auth/useRegisterForm';

const { form, validateField, submit } = useRegisterForm();
</script>

<template>
    <Head title="Criar conta" />

    <GuestLayout title="Crie sua conta" subtitle="Comece a organizar suas vistorias solares em poucos minutos." icon="mdi:account-plus-outline">
        <form class="space-y-4" novalidate @submit.prevent="submit">
            <AppFormField label="Nome" :error="form.errors.name">
                <AppTextInput
                    v-model="form.name"
                    icon="mdi:account-outline"
                    :error="form.errors.name"
                    :maxlength="FIELD_LIMITS.NAME_MAX"
                    placeholder="Seu nome completo"
                    autocomplete="name"
                    required
                    autofocus
                    @blur="validateField('name')"
                />
            </AppFormField>

            <AppFormField label="E-mail" :error="form.errors.email">
                <AppTextInput
                    v-model="form.email"
                    icon="mdi:email-outline"
                    :error="form.errors.email"
                    :maxlength="FIELD_LIMITS.EMAIL_MAX"
                    type="email"
                    placeholder="seu@email.com"
                    autocomplete="username"
                    required
                    @blur="validateField('email')"
                />
            </AppFormField>

            <AppFormField label="Nome da empresa" :error="form.errors.company_name">
                <AppTextInput
                    v-model="form.company_name"
                    icon="mdi:domain"
                    :error="form.errors.company_name"
                    :maxlength="FIELD_LIMITS.COMPANY_NAME_MAX"
                    placeholder="Como sua empresa é conhecida"
                    autocomplete="organization"
                    required
                    @blur="validateField('company_name')"
                />
            </AppFormField>

            <AppFormField label="Senha" :error="form.errors.password">
                <AppPasswordInput
                    v-model="form.password"
                    :error="form.errors.password"
                    :placeholder="`Mínimo de ${FIELD_LIMITS.PASSWORD_MIN} caracteres`"
                    autocomplete="new-password"
                    required
                    @blur="validateField('password')"
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
                    @blur="validateField('password_confirmation')"
                />
            </AppFormField>

            <button type="submit" class="btn btn-primary btn-block mt-2 h-12 rounded-full shadow-lg shadow-brand-700/20" :disabled="form.processing">
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                Criar conta
                <Icon v-if="!form.processing" icon="mdi:arrow-right" class="size-5" />
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-ink-muted">
            Já tem uma conta?
            <Link :href="route(ROUTE_NAMES_AUTH.LOGIN)" class="link link-hover link-primary font-medium">Entrar</Link>
        </p>
    </GuestLayout>
</template>

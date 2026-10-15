<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import { FIELD_LIMITS, ROUTE_NAMES_AUTH } from '@/Constants';
import { useForgotPasswordForm } from '@/Composables/auth/useForgotPasswordForm';

withDefaults(defineProps<{ status?: string }>(), { status: '' });

const { form, validateField, submit } = useForgotPasswordForm();
</script>

<template>
    <Head title="Recuperar senha" />

    <GuestLayout
        title="Esqueceu a senha?"
        subtitle="Sem problemas. Informe seu e-mail e enviaremos um link para você cadastrar uma nova senha."
        icon="mdi:lock-reset"
    >
        <div v-if="status" role="alert" class="alert alert-success alert-soft mb-6 text-sm">
            <Icon icon="mdi:email-check-outline" class="size-5" />
            {{ status }}
        </div>

        <form class="space-y-4" novalidate @submit.prevent="submit">
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
                    autofocus
                    @blur="validateField('email')"
                />
            </AppFormField>

            <button type="submit" class="btn btn-primary btn-block mt-2 h-12 rounded-full shadow-lg shadow-brand-700/20" :disabled="form.processing">
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                <Icon v-else icon="mdi:send-outline" class="size-5" />
                Enviar link de recuperação
            </button>
        </form>

        <Link :href="route(ROUTE_NAMES_AUTH.LOGIN)" class="btn btn-ghost btn-block mt-6 rounded-full text-ink-muted">
            <Icon icon="mdi:arrow-left" class="size-4" />
            Voltar ao login
        </Link>
    </GuestLayout>
</template>

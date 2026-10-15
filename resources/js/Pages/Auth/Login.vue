<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import AppPasswordInput from '@/Components/common/AppPasswordInput.vue';
import { FIELD_LIMITS, ROUTE_NAMES_AUTH } from '@/Constants';
import { useLoginForm } from '@/Composables/auth/useLoginForm';

withDefaults(defineProps<{ canResetPassword?: boolean; status?: string }>(), { canResetPassword: true, status: '' });

const { form, validateField, submit } = useLoginForm();
</script>

<template>
    <Head title="Entrar" />

    <GuestLayout title="Bem-vindo de volta" subtitle="Entre na sua conta para gerenciar suas vistorias." icon="mdi:login">
        <div v-if="status" role="alert" class="alert alert-success alert-soft mb-6 text-sm">
            <Icon icon="mdi:check-circle-outline" class="size-5" />
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

            <AppFormField label="Senha" :error="form.errors.password">
                <AppPasswordInput
                    v-model="form.password"
                    :error="form.errors.password"
                    placeholder="Sua senha"
                    required
                    @blur="validateField('password')"
                />
            </AppFormField>

            <div class="flex items-center justify-between text-sm">
                <label class="flex cursor-pointer items-center gap-2 text-ink-soft">
                    <input v-model="form.remember" type="checkbox" class="checkbox checkbox-primary checkbox-sm rounded-md" />
                    Lembrar de mim
                </label>

                <Link v-if="canResetPassword" :href="route(ROUTE_NAMES_AUTH.PASSWORD_REQUEST)" class="link link-hover link-primary font-medium">
                    Esqueceu a senha?
                </Link>
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-2 h-12 rounded-full shadow-lg shadow-brand-700/20" :disabled="form.processing">
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                Entrar
                <Icon v-if="!form.processing" icon="mdi:arrow-right" class="size-5" />
            </button>
        </form>

        <div class="divider my-8 text-xs text-ink-subtle">Novo por aqui?</div>

        <Link :href="route(ROUTE_NAMES_AUTH.REGISTER)" class="btn btn-outline btn-primary btn-block h-12 rounded-full">
            Criar uma conta
        </Link>
    </GuestLayout>
</template>

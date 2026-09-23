<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import FormField from '@/Components/FormField.vue';
import TextInput from '@/Components/TextInput.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import PasswordStrength from '@/Components/PasswordStrength.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Criar conta" />

    <GuestLayout
        title="Crie sua conta"
        subtitle="Comece a organizar suas vistorias solares em poucos minutos."
        icon="mdi:account-plus-outline"
    >
        <form class="space-y-4" @submit.prevent="submit">
            <FormField label="Nome" :error="form.errors.name">
                <TextInput
                    v-model="form.name"
                    icon="mdi:account-outline"
                    :error="form.errors.name"
                    type="text"
                    placeholder="Seu nome completo"
                    autocomplete="name"
                    required
                    autofocus
                />
            </FormField>

            <FormField label="E-mail" :error="form.errors.email">
                <TextInput
                    v-model="form.email"
                    icon="mdi:email-outline"
                    :error="form.errors.email"
                    type="email"
                    placeholder="seu@email.com"
                    autocomplete="username"
                    required
                />
            </FormField>

            <FormField label="Senha" :error="form.errors.password">
                <PasswordInput
                    v-model="form.password"
                    :error="form.errors.password"
                    placeholder="Mínimo de 8 caracteres"
                    autocomplete="new-password"
                    required
                />
                <PasswordStrength :password="form.password" />
            </FormField>

            <FormField label="Confirmar senha" :error="form.errors.password_confirmation">
                <PasswordInput
                    v-model="form.password_confirmation"
                    :error="form.errors.password_confirmation"
                    placeholder="Repita a senha"
                    autocomplete="new-password"
                    required
                />
            </FormField>

            <button
                type="submit"
                class="btn btn-primary btn-block mt-2 h-12 rounded-full shadow-lg shadow-brand-700/20"
                :disabled="form.processing"
            >
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                Criar conta
                <Icon v-if="!form.processing" icon="mdi:arrow-right" class="size-5" />
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-ink-muted">
            Já tem uma conta?
            <Link :href="route('login')" class="link link-hover link-primary font-medium">Entrar</Link>
        </p>
    </GuestLayout>
</template>

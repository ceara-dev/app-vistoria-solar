<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import FormField from '@/Components/FormField.vue';
import TextInput from '@/Components/TextInput.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import PasswordStrength from '@/Components/PasswordStrength.vue';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Nova senha" />

    <GuestLayout title="Crie uma nova senha" subtitle="Escolha uma senha forte, com pelo menos 8 caracteres." icon="mdi:shield-key-outline">
        <form class="space-y-4" @submit.prevent="submit">
            <FormField label="E-mail" :error="form.errors.email">
                <TextInput
                    v-model="form.email"
                    icon="mdi:email-outline"
                    :error="form.errors.email"
                    type="email"
                    autocomplete="username"
                    required
                />
            </FormField>

            <FormField label="Nova senha" :error="form.errors.password">
                <PasswordInput
                    v-model="form.password"
                    :error="form.errors.password"
                    placeholder="Mínimo de 8 caracteres"
                    autocomplete="new-password"
                    required
                    autofocus
                />
                <PasswordStrength :password="form.password" />
            </FormField>

            <FormField label="Confirmar nova senha" :error="form.errors.password_confirmation">
                <PasswordInput
                    v-model="form.password_confirmation"
                    :error="form.errors.password_confirmation"
                    placeholder="Repita a nova senha"
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
                Salvar nova senha
            </button>
        </form>

        <Link :href="route('login')" class="btn btn-ghost btn-block mt-6 rounded-full text-ink-muted">
            <Icon icon="mdi:arrow-left" class="size-4" />
            Voltar ao login
        </Link>
    </GuestLayout>
</template>

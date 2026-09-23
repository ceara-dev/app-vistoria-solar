<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import FormField from '@/Components/FormField.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    status: { type: String, default: '' },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
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

        <form class="space-y-4" @submit.prevent="submit">
            <FormField label="E-mail" :error="form.errors.email">
                <TextInput
                    v-model="form.email"
                    icon="mdi:email-outline"
                    :error="form.errors.email"
                    type="email"
                    placeholder="seu@email.com"
                    autocomplete="username"
                    required
                    autofocus
                />
            </FormField>

            <button
                type="submit"
                class="btn btn-primary btn-block mt-2 h-12 rounded-full shadow-lg shadow-brand-700/20"
                :disabled="form.processing"
            >
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                <Icon v-else icon="mdi:send-outline" class="size-5" />
                Enviar link de recuperação
            </button>
        </form>

        <Link :href="route('login')" class="btn btn-ghost btn-block mt-6 rounded-full text-ink-muted">
            <Icon icon="mdi:arrow-left" class="size-4" />
            Voltar ao login
        </Link>
    </GuestLayout>
</template>

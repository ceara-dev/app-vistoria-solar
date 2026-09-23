<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import FormField from '@/Components/FormField.vue';
import PasswordInput from '@/Components/PasswordInput.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Confirmar senha" />

    <GuestLayout
        title="Confirme sua senha"
        subtitle="Esta é uma área segura do sistema. Confirme sua senha para continuar."
        icon="mdi:shield-lock-outline"
    >
        <form class="space-y-4" @submit.prevent="submit">
            <FormField label="Senha" :error="form.errors.password">
                <PasswordInput v-model="form.password" :error="form.errors.password" placeholder="Sua senha" required autofocus />
            </FormField>

            <button
                type="submit"
                class="btn btn-primary btn-block mt-2 h-12 rounded-full shadow-lg shadow-brand-700/20"
                :disabled="form.processing"
            >
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                Confirmar
            </button>
        </form>
    </GuestLayout>
</template>

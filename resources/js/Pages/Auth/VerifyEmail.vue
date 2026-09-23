<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    status: { type: String, default: '' },
});

const form = useForm({});

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <Head title="Verificar e-mail" />

    <GuestLayout
        title="Verifique seu e-mail"
        subtitle="Obrigado por se cadastrar! Enviamos um link de confirmação para o seu e-mail. Clique nele para ativar sua conta."
        icon="mdi:email-fast-outline"
    >
        <div v-if="verificationLinkSent" role="alert" class="alert alert-success alert-soft mb-6 text-sm">
            <Icon icon="mdi:email-check-outline" class="size-5" />
            Um novo link de verificação foi enviado para o e-mail cadastrado.
        </div>

        <div class="mb-6 rounded-box bg-base-200 p-4 text-sm text-ink-muted">
            Não recebeu? Confira a caixa de spam ou peça um novo link abaixo.
        </div>

        <form @submit.prevent="submit">
            <button
                type="submit"
                class="btn btn-primary btn-block h-12 rounded-full shadow-lg shadow-brand-700/20"
                :disabled="form.processing"
            >
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                <Icon v-else icon="mdi:email-sync-outline" class="size-5" />
                Reenviar e-mail de verificação
            </button>
        </form>

        <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="btn btn-ghost btn-block mt-4 rounded-full text-ink-muted"
        >
            <Icon icon="mdi:logout" class="size-4" />
            Sair
        </Link>
    </GuestLayout>
</template>

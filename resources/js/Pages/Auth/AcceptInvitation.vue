<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import NewAccountFields from '@/Components/auth/NewAccountFields.vue';
import { ROUTE_NAMES_AUTH } from '@/Constants';
import { useInvitationAcceptance } from '@/Composables/auth/useInvitationAcceptance';
import type { InvitationAcceptanceMode, InvitationSummary } from '@/Types/auth';

const props = defineProps<{ token: string; mode: InvitationAcceptanceMode; invitation: InvitationSummary }>();

const { form, validateField, accept } = useInvitationAcceptance(props.token, props.mode);
</script>

<template>
    <Head title="Convite" />

    <GuestLayout
        :title="`Convite para ${invitation.tenant_name}`"
        :subtitle="`Você foi convidado como ${invitation.role_label}, no e-mail ${invitation.email}.`"
        icon="mdi:email-open-outline"
    >
        <div v-if="$page.props.errors.invitation" role="alert" class="alert alert-error alert-soft mb-6 text-sm">
            <Icon icon="mdi:alert-circle-outline" class="size-5" />
            {{ $page.props.errors.invitation }}
        </div>

        <form v-if="mode === 'create_account' || mode === 'accept'" class="space-y-4" novalidate @submit.prevent="accept">
            <NewAccountFields v-if="mode === 'create_account'" :form="form" @validate="validateField" />

            <button type="submit" class="btn btn-primary btn-block h-12 rounded-full" :disabled="form.processing">
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                {{ mode === 'create_account' ? 'Criar conta e entrar' : 'Aceitar convite' }}
            </button>
        </form>

        <template v-else-if="mode === 'login'">
            <p class="text-sm text-ink-muted">Você já tem uma conta com este e-mail. Entre para aceitar o convite.</p>
            <Link :href="route(ROUTE_NAMES_AUTH.LOGIN)" class="btn btn-primary btn-block mt-6 h-12 rounded-full">Entrar</Link>
        </template>

        <template v-else>
            <p class="text-sm text-ink-muted">Você está conectado com outro e-mail. Saia e entre com {{ invitation.email }} para aceitar.</p>
            <Link :href="route(ROUTE_NAMES_AUTH.LOGOUT)" method="post" as="button" class="btn btn-primary btn-block mt-6 h-12 rounded-full">
                Sair
            </Link>
        </template>
    </GuestLayout>
</template>

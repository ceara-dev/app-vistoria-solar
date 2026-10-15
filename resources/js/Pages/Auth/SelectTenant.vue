<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import { FIELD_LIMITS, ROUTE_NAMES_AUTH } from '@/Constants';
import { useTenantSelection } from '@/Composables/auth/useTenantSelection';
import type { ResourceCollection, TenantSummary } from '@/Types/shared';

defineProps<{ tenants: ResourceCollection<TenantSummary> }>();

const { form, validateField, chooseTenant, createTenant } = useTenantSelection();
</script>

<template>
    <Head title="Escolher empresa" />

    <GuestLayout
        :title="tenants.data.length > 0 ? 'Em qual empresa você vai trabalhar?' : 'Você ainda não está em nenhuma empresa'"
        :subtitle="tenants.data.length > 0 ? 'Você pode trocar de empresa depois, no topo da tela.' : 'Peça um convite a quem administra a sua empresa ou crie uma nova.'"
        icon="mdi:domain"
    >
        <ul v-if="tenants.data.length > 0" class="space-y-2">
            <li v-for="tenant in tenants.data" :key="tenant.id">
                <button type="button" class="btn btn-outline btn-block h-12 justify-between rounded-xl" @click="chooseTenant(tenant.id)">
                    {{ tenant.name }}
                    <Icon icon="mdi:arrow-right" class="size-5" />
                </button>
            </li>
        </ul>

        <div class="divider my-8 text-xs text-ink-subtle">ou crie uma empresa</div>

        <form class="space-y-4" novalidate @submit.prevent="createTenant">
            <AppFormField label="Nome da empresa" :error="form.errors.company_name">
                <AppTextInput
                    v-model="form.company_name"
                    icon="mdi:domain"
                    :error="form.errors.company_name"
                    :maxlength="FIELD_LIMITS.COMPANY_NAME_MAX"
                    autocomplete="organization"
                    required
                    @blur="validateField('company_name')"
                />
            </AppFormField>

            <button type="submit" class="btn btn-primary btn-block h-12 rounded-full" :disabled="form.processing">
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                Criar empresa
            </button>
        </form>

        <Link :href="route(ROUTE_NAMES_AUTH.LOGOUT)" method="post" as="button" class="btn btn-ghost btn-block mt-6 rounded-full text-ink-muted">
            <Icon icon="mdi:logout" class="size-4" />
            Sair
        </Link>
    </GuestLayout>
</template>

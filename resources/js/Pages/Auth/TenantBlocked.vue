<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { ROUTE_NAMES_AUTH } from '@/Constants';

defineProps<{ tenantName: string; reason: string; hasOtherTenants: boolean }>();
</script>

<template>
    <Head title="Empresa sem acesso" />

    <GuestLayout :title="tenantName" :subtitle="reason" icon="mdi:lock-outline">
        <div class="rounded-box bg-base-200 p-4 text-sm text-ink-muted">
            Para voltar a usar o sistema nesta empresa, fale com o proprietário dela ou com o suporte do Vistoria Solar.
        </div>

        <Link v-if="hasOtherTenants" :href="route(ROUTE_NAMES_AUTH.TENANT_SELECT)" class="btn btn-primary btn-block mt-6 h-12 rounded-full">
            <Icon icon="mdi:swap-horizontal" class="size-5" />
            Trabalhar em outra empresa
        </Link>

        <Link :href="route(ROUTE_NAMES_AUTH.LOGOUT)" method="post" as="button" class="btn btn-ghost btn-block mt-4 rounded-full text-ink-muted">
            <Icon icon="mdi:logout" class="size-4" />
            Sair
        </Link>
    </GuestLayout>
</template>

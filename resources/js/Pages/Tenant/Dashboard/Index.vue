<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
</script>

<template>
    <Head title="Painel" />

    <TenantLayout :title="`Olá, ${$page.props.auth.user?.name}!`" :subtitle="`Você está trabalhando em ${$page.props.tenant?.name}.`" />
</template>

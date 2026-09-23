<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
</script>

<template>
    <Head title="Painel" />

    <div class="min-h-screen bg-base-200">
        <header class="navbar bg-base-100 px-4 shadow-sm sm:px-6">
            <div class="flex-1">
                <Link :href="route('dashboard')" class="block w-36">
                    <img src="/images/logo.png" alt="Vistoria Solar" class="w-full" />
                </Link>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-ink-soft sm:inline">{{ $page.props.auth.user.name }}</span>
                <Link :href="route('logout')" method="post" as="button" class="btn btn-ghost btn-sm">
                    <Icon icon="mdi:logout" class="size-5" />
                    Sair
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
            <h1 class="text-2xl font-bold text-ink">Olá, {{ $page.props.auth.user.name }}!</h1>
            <p class="mt-2 text-ink-muted">Você está conectado ao Vistoria Solar.</p>
        </main>
    </div>
</template>

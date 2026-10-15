<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { highlights, panelBadges } from '@/Constants/auth';

withDefaults(defineProps<{ title: string; subtitle?: string; icon?: string }>(), {
    subtitle: '',
    icon: 'mdi:solar-power-variant-outline',
});
</script>

<template>
    <div class="relative flex min-h-screen overflow-hidden bg-base-100 lg:p-4">
        <!-- Formas orgânicas de fundo -->
        <div class="pointer-events-none absolute -right-40 -bottom-40 size-128 rounded-full bg-brand-50" />
        <div class="pointer-events-none absolute -top-24 right-1/4 size-64 rounded-full bg-brand-50/60 lg:hidden" />

        <!-- Painel da marca -->
        <aside
            class="relative hidden w-[46%] flex-col justify-between overflow-hidden rounded-4xl bg-brand-gradient bg-size-[400%_400%] p-12 text-white animate-gradient lg:flex"
        >
            <div class="pointer-events-none absolute -top-32 -right-32 size-96 rounded-full bg-white/10" />
            <div class="pointer-events-none absolute -bottom-24 -left-24 size-72 rounded-full bg-white/10" />

            <Link href="/" class="relative font-mono text-2xl drop-shadow-md">
                Vistoria <span class="tracking-[6px] uppercase">Solar</span>
            </Link>

            <div class="relative">
                <p class="text-xs font-semibold tracking-widest text-white/80 uppercase">Uma plataforma de vistoria solar</p>
                <h2 class="mt-3 text-4xl leading-tight font-bold text-balance drop-shadow-md">Nossa fonte de energia é o sol</h2>

                <div class="relative mx-auto mt-10 max-w-md">
                    <img src="/images/notebook.png" alt="Plataforma Vistoria Solar" class="w-full drop-shadow-2xl" />
                    <span
                        v-for="badge in panelBadges"
                        :key="badge.icon"
                        class="absolute flex size-12 animate-float items-center justify-center rounded-2xl shadow-lg"
                        :class="badge.class"
                        :style="{ animationDelay: badge.delay }"
                    >
                        <Icon :icon="badge.icon" class="size-6" />
                    </span>
                </div>
            </div>

            <ul class="relative space-y-3">
                <li v-for="item in highlights" :key="item.text" class="flex items-center gap-3 text-sm font-medium">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-white/15">
                        <Icon :icon="item.icon" class="size-4" />
                    </span>
                    {{ item.text }}
                </li>
            </ul>
        </aside>

        <!-- Formulário -->
        <main class="relative flex flex-1 flex-col px-6 py-6 sm:px-12">
            <div class="flex items-center justify-between">
                <Link href="/" class="block w-32 lg:invisible">
                    <img src="/images/logo.png" alt="Vistoria Solar" class="w-full" />
                </Link>
                <Link href="/" class="btn btn-ghost btn-sm rounded-full text-ink-muted">
                    <Icon icon="mdi:arrow-left" class="size-4" />
                    Voltar ao site
                </Link>
            </div>

            <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-10">
                <span class="flex size-14 items-center justify-center rounded-2xl bg-brand-50 text-primary">
                    <Icon :icon="icon" class="size-7" />
                </span>

                <h1 class="mt-6 text-3xl font-bold text-ink">{{ title }}</h1>
                <p v-if="subtitle" class="mt-2 text-ink-muted">{{ subtitle }}</p>

                <div class="mt-8">
                    <slot />
                </div>
            </div>

            <p class="text-center text-xs text-ink-subtle">&copy; {{ new Date().getFullYear() }} Exata Engenharia</p>
        </main>
    </div>
</template>

<script setup lang="ts">
import { useTemplateRef } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AppHighlightCard from '@/Components/common/AppHighlightCard.vue';
import AppUserMenu from '@/Components/common/AppUserMenu.vue';
import CentralNavLinks from '@/Components/central/CentralNavLinks.vue';
import { ROUTE_NAMES_CENTRAL } from '@/Constants';
import { useFlashToast } from '@/Composables/shared/useFlashToast';

withDefaults(defineProps<{ title: string; subtitle?: string }>(), { subtitle: '' });

const { toast } = useFlashToast();

const drawerToggle = useTemplateRef<HTMLInputElement>('drawerToggle');

function closeDrawer(): void {
    if (drawerToggle.value) {
        drawerToggle.value.checked = false;
    }
}
</script>

<template>
    <div class="drawer lg:drawer-open">
        <input ref="drawerToggle" id="central-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex min-h-screen min-w-0 flex-col bg-base-200">
            <header class="sticky top-0 z-10 flex items-center gap-3 border-b border-base-300 bg-base-100/90 px-4 py-3 backdrop-blur lg:px-6">
                <label for="central-drawer" class="btn btn-square btn-ghost lg:hidden" aria-label="Abrir menu">
                    <Icon icon="mdi:menu" class="size-6" />
                </label>

                <span class="flex-1"></span>

                <AppUserMenu />
            </header>

            <main class="flex-1 px-4 py-6 lg:px-6 lg:py-8">
                <div
                    v-if="toast"
                    role="alert"
                    class="alert alert-soft mb-6 text-sm"
                    :class="toast.type === 'success' ? 'alert-success' : 'alert-error'"
                >
                    <Icon :icon="toast.type === 'success' ? 'mdi:check-circle-outline' : 'mdi:alert-circle-outline'" class="size-5" />
                    {{ toast.message }}
                </div>

                <h1 class="text-2xl font-bold text-ink">{{ title }}</h1>
                <p v-if="subtitle" class="mt-2 text-ink-muted">{{ subtitle }}</p>

                <div class="mt-8">
                    <slot />
                </div>
            </main>
        </div>

        <div class="drawer-side z-30">
            <label for="central-drawer" aria-label="Fechar menu" class="drawer-overlay"></label>

            <aside class="flex min-h-full w-72 flex-col gap-5 border-r border-base-300 bg-base-100 p-4">
                <Link :href="route(ROUTE_NAMES_CENTRAL.DASHBOARD)" class="flex items-center px-1 pt-1" @click="closeDrawer">
                    <img src="/images/logo.png" alt="Vistoria Solar" class="w-32" />
                </Link>

                <AppHighlightCard icon="mdi:shield-account-outline" label="Painel" title="Central da plataforma" />

                <ul class="menu w-full gap-1 p-0" @click="closeDrawer">
                    <CentralNavLinks />
                </ul>

                <div class="mt-auto rounded-box border border-brand-100 bg-brand-50 p-4">
                    <p class="text-sm font-semibold text-brand-900">Nova empresa?</p>
                    <p class="mt-1 text-xs text-ink-muted">Cadastre a empresa e convide o administrador.</p>
                    <Link
                        :href="route(ROUTE_NAMES_CENTRAL.TENANTS.CREATE)"
                        class="btn btn-primary btn-sm mt-3 w-full"
                        @click="closeDrawer"
                    >
                        <Icon icon="mdi:plus" class="size-4" />
                        Nova empresa
                    </Link>
                </div>
            </aside>
        </div>
    </div>
</template>

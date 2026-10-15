<script setup lang="ts">
import { useTemplateRef } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AppHighlightCard from '@/Components/common/AppHighlightCard.vue';
import AppUserMenu from '@/Components/common/AppUserMenu.vue';
import TenantNavLinks from '@/Components/tenant/TenantNavLinks.vue';
import { PERMISSIONS, ROUTE_NAMES_TENANT } from '@/Constants';
import { useAbilities } from '@/Composables/shared/useAbilities';
import { useFlashToast } from '@/Composables/shared/useFlashToast';
import { useTenantSwitcher } from '@/Composables/tenant/useTenantSwitcher';

withDefaults(defineProps<{ title: string; subtitle?: string }>(), { subtitle: '' });

const { toast } = useFlashToast();
const { currentTenant, otherTenants, switchTenant } = useTenantSwitcher();
const { can } = useAbilities();

const drawerToggle = useTemplateRef<HTMLInputElement>('drawerToggle');

function closeDrawer(): void {
    if (drawerToggle.value) {
        drawerToggle.value.checked = false;
    }
}

function switchTo(tenantId: number): void {
    closeDrawer();
    switchTenant(tenantId);
}
</script>

<template>
    <div class="drawer lg:drawer-open">
        <input ref="drawerToggle" id="tenant-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex min-h-screen min-w-0 flex-col bg-base-200">
            <header class="sticky top-0 z-10 flex items-center gap-3 border-b border-base-300 bg-base-100/90 px-4 py-3 backdrop-blur lg:px-6">
                <label for="tenant-drawer" class="btn btn-square btn-ghost lg:hidden" aria-label="Abrir menu">
                    <Icon icon="mdi:menu" class="size-6" />
                </label>

                <span class="flex-1"></span>

                <AppUserMenu :subtitle="currentTenant?.name" />
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
            <label for="tenant-drawer" aria-label="Fechar menu" class="drawer-overlay"></label>

            <aside class="flex min-h-full w-72 flex-col gap-5 border-r border-base-300 bg-base-100 p-4">
                <Link :href="route(ROUTE_NAMES_TENANT.DASHBOARD)" class="flex items-center px-1 pt-1" @click="closeDrawer">
                    <img src="/images/logo.png" alt="Vistoria Solar" class="w-32" />
                </Link>

                <div v-if="otherTenants.length > 0" class="dropdown w-full">
                    <div tabindex="0" role="button" aria-label="Trocar de empresa">
                        <AppHighlightCard
                            icon="mdi:domain"
                            label="Empresa atual"
                            :title="currentTenant?.name ?? ''"
                            trailing-icon="mdi:chevron-down"
                        />
                    </div>
                    <ul tabindex="0" class="menu dropdown-content z-20 mt-2 w-full rounded-box border border-base-300 bg-base-100 p-2 shadow-lg">
                        <li class="menu-title">Trocar de empresa</li>
                        <li v-for="tenant in otherTenants" :key="tenant.id">
                            <button type="button" class="gap-3 py-2.5" @click="switchTo(tenant.id)">{{ tenant.name }}</button>
                        </li>
                    </ul>
                </div>
                <AppHighlightCard v-else icon="mdi:domain" label="Empresa atual" :title="currentTenant?.name ?? ''" />

                <ul class="menu w-full gap-1 p-0" @click="closeDrawer">
                    <TenantNavLinks />
                </ul>

                <div v-if="can(PERMISSIONS.CUSTOMERS_MANAGE)" class="mt-auto rounded-box border border-brand-100 bg-brand-50 p-4">
                    <p class="text-sm font-semibold text-brand-900">Cadastre um cliente</p>
                    <p class="mt-1 text-xs text-ink-muted">Quem tem sistemas fotovoltaicos acompanhados pela empresa.</p>
                    <Link
                        :href="route(ROUTE_NAMES_TENANT.CUSTOMERS.CREATE)"
                        class="btn btn-primary btn-sm mt-3 w-full"
                        @click="closeDrawer"
                    >
                        <Icon icon="mdi:account-plus-outline" class="size-4" />
                        Novo cliente
                    </Link>
                </div>
            </aside>
        </div>
    </div>
</template>

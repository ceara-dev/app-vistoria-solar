<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { ROUTE_NAMES_AUTH } from '@/Constants';
import { initials } from '@/Utils';

defineProps<{ subtitle?: string }>();

const page = usePage();
</script>

<template>
    <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-ghost h-auto gap-3 px-2 py-1">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-50 text-xs font-semibold text-brand-900">
                {{ initials(page.props.auth.user?.name ?? '') }}
            </span>
            <span class="hidden flex-col items-start leading-tight sm:flex">
                <span class="text-sm font-semibold text-ink">{{ page.props.auth.user?.name }}</span>
                <span v-if="subtitle" class="max-w-40 truncate text-xs font-normal text-ink-muted">{{ subtitle }}</span>
            </span>
            <Icon icon="mdi:chevron-down" class="hidden size-4 text-ink-subtle sm:block" />
        </div>
        <ul tabindex="0" class="menu dropdown-content z-20 mt-2 w-56 rounded-box border border-base-300 bg-base-100 p-2 shadow-lg">
            <li class="menu-title truncate">{{ page.props.auth.user?.email }}</li>
            <li>
                <Link :href="route(ROUTE_NAMES_AUTH.LOGOUT)" method="post" as="button">
                    <Icon icon="mdi:logout" class="size-4" />
                    Sair
                </Link>
            </li>
        </ul>
    </div>
</template>

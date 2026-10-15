<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { CENTRAL_NAVIGATION } from '@/Constants';
</script>

<template>
    <li v-for="item in CENTRAL_NAVIGATION" :key="item.route">
        <Link
            :href="route(item.route)"
            class="gap-3 py-2.5"
            :class="{ 'bg-brand-50 font-semibold text-brand-900': route().current(item.activePattern) }"
        >
            <Icon :icon="item.icon" class="size-5" />
            {{ item.label }}
        </Link>
    </li>
</template>

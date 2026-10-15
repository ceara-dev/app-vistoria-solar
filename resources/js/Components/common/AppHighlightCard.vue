<script setup lang="ts">
import { Icon } from '@iconify/vue';

withDefaults(defineProps<{ icon: string; label: string; title: string; trailingIcon?: string }>(), {
    trailingIcon: '',
});
</script>

<template>
    <div class="flex w-full items-center gap-3 rounded-box bg-gradient-to-br from-brand-600 to-brand-800 p-3 text-white shadow-md">
        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-white/15">
            <Icon :icon="icon" class="size-5" />
        </span>
        <span class="min-w-0 flex-1 leading-tight">
            <span class="block text-xs text-white/75">{{ label }}</span>
            <span class="block truncate text-sm font-semibold">{{ title }}</span>
        </span>
        <Icon v-if="trailingIcon" :icon="trailingIcon" class="size-4 shrink-0 text-white/75" />
    </div>
</template>

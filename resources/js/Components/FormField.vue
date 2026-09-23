<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    label: { type: String, required: true },
    error: { type: String, default: '' },
});
</script>

<template>
    <fieldset class="fieldset gap-1.5">
        <legend class="fieldset-legend text-sm font-medium text-ink">{{ label }}</legend>
        <slot />
        <p v-if="error" class="flex items-center gap-1 text-xs text-error">
            <Icon icon="mdi:alert-circle-outline" class="size-4" />
            {{ error }}
        </p>
    </fieldset>
</template>

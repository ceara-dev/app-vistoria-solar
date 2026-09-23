<script setup>
import { Icon } from '@iconify/vue';

defineOptions({ inheritAttrs: false });

const model = defineModel({ type: String, default: '' });

defineProps({
    icon: { type: String, default: '' },
    error: { type: String, default: '' },
});
</script>

<template>
    <label class="input h-12 w-full rounded-xl" :class="{ 'input-error': error }">
        <Icon v-if="icon" :icon="icon" class="size-5 opacity-50" />
        <input v-model="model" v-bind="$attrs" class="grow" />
    </label>
</template>

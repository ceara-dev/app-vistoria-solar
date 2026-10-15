<script setup lang="ts" generic="TValue extends string | number">
import type { SelectOption } from '@/Types/shared';

defineOptions({ inheritAttrs: false });

const model = defineModel<TValue>();

withDefaults(defineProps<{ options: SelectOption<TValue>[]; error?: string }>(), { error: '' });
</script>

<template>
    <select
        v-model="model"
        v-bind="$attrs"
        :aria-invalid="error ? 'true' : undefined"
        class="select h-12 w-full rounded-xl"
        :class="{ 'select-error': error }"
    >
        <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
    </select>
</template>

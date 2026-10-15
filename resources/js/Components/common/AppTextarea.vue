<script setup lang="ts">
import { computed } from 'vue';

defineOptions({ inheritAttrs: false });

const model = defineModel<string>({ default: '' });

const props = withDefaults(defineProps<{ error?: string; maxlength: number }>(), { error: '' });

// Mesmas cores do contador do AppTextInput (.docs/VALIDATION.md).
const counterClass = computed(() => {
    const usage = model.value.length / props.maxlength;

    if (usage >= 1) {
        return 'text-error';
    }

    return usage >= 0.8 ? 'text-warning' : 'text-ink-subtle';
});
</script>

<template>
    <div class="relative w-full">
        <textarea
            v-model="model"
            v-bind="$attrs"
            :maxlength="maxlength"
            :aria-invalid="error ? 'true' : undefined"
            class="textarea min-h-28 w-full rounded-xl pb-7"
            :class="{ 'textarea-error': error }"
        />
        <span class="absolute right-3 bottom-2 text-xs tabular-nums" :class="counterClass">{{ model.length }}/{{ maxlength }}</span>
    </div>
</template>

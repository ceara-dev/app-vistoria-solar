<script setup lang="ts">
import { computed } from 'vue';
import { Icon } from '@iconify/vue';

defineOptions({ inheritAttrs: false });

// Campos `type="number"` com `v-model` devolvem número; vazio continua texto.
const model = defineModel<string | number>({ default: '' });

const props = withDefaults(defineProps<{ icon?: string; error?: string; maxlength?: number }>(), {
    icon: '',
    error: '',
    maxlength: undefined,
});

// Contador obrigatório quando há limite (.docs/VALIDATION.md): cinza, amarelo a partir de 80% e vermelho no limite.
const counterClass = computed(() => {
    if (props.maxlength === undefined) {
        return '';
    }

    const usage = String(model.value).length / props.maxlength;

    if (usage >= 1) {
        return 'text-error';
    }

    return usage >= 0.8 ? 'text-warning' : 'text-ink-subtle';
});
</script>

<template>
    <label class="input h-12 w-full rounded-xl" :class="{ 'input-error': error }">
        <Icon v-if="icon" :icon="icon" class="size-5 opacity-50" />
        <input v-model="model" v-bind="$attrs" :maxlength="maxlength" :aria-invalid="error ? 'true' : undefined" class="grow" />
        <span v-if="maxlength !== undefined" class="text-xs tabular-nums" :class="counterClass">
            {{ String(model).length }}/{{ maxlength }}
        </span>
    </label>
</template>

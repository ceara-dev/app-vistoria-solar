<script setup>
import { ref } from 'vue';
import { Icon } from '@iconify/vue';

defineOptions({ inheritAttrs: false });

const model = defineModel({ type: String, default: '' });

defineProps({
    error: { type: String, default: '' },
});

const visible = ref(false);
</script>

<template>
    <label class="input h-12 w-full rounded-xl" :class="{ 'input-error': error }">
        <Icon icon="mdi:lock-outline" class="size-5 opacity-50" />
        <input
            v-model="model"
            autocomplete="current-password"
            v-bind="$attrs"
            :type="visible ? 'text' : 'password'"
            class="grow"
        />
        <button
            type="button"
            class="cursor-pointer text-ink-subtle transition hover:text-primary"
            :aria-label="visible ? 'Ocultar senha' : 'Mostrar senha'"
            @click="visible = !visible"
        >
            <Icon :icon="visible ? 'mdi:eye-outline' : 'mdi:eye-off-outline'" class="size-5" />
        </button>
    </label>
</template>

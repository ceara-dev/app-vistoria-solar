<script setup>
import { computed } from 'vue';
import { passwordStrengthLevels } from '@/Constants/auth';

const props = defineProps({
    password: { type: String, default: '' },
});

// Pontua de 0 a 4: tamanho, maiúsculas/minúsculas, números e símbolos
const score = computed(() => {
    const password = props.password;
    let points = 0;

    if (password.length >= 8) points++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) points++;
    if (/\d/.test(password)) points++;
    if (/[^A-Za-z0-9]/.test(password)) points++;

    return password.length >= 12 ? Math.min(points + 1, 4) : points;
});

const level = computed(() => passwordStrengthLevels[score.value]);
</script>

<template>
    <div v-if="password" class="mt-1 flex items-center gap-3">
        <progress class="progress h-1.5 flex-1" :class="level.class" :value="score + 1" max="5" />
        <span class="w-20 text-right text-xs text-ink-muted">{{ level.label }}</span>
    </div>
</template>

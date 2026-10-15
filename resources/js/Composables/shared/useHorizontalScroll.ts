import { ref } from 'vue';

/**
 * Rola um contêiner horizontal (carrossel) para os lados.
 */
export function useHorizontalScroll(step = 320) {
    const container = ref<HTMLElement | null>(null);

    function scroll(direction: 1 | -1): void {
        container.value?.scrollBy({ left: direction * step, behavior: 'smooth' });
    }

    return { container, scroll };
}

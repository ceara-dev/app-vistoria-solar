<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Paginated } from '@/Types/shared';

defineProps<{ meta: Paginated<unknown>['meta'] }>();
</script>

<template>
    <nav v-if="meta.last_page > 1" class="flex flex-wrap items-center justify-between gap-3 text-sm" aria-label="Paginação">
        <p class="text-ink-muted">{{ meta.from }}–{{ meta.to }} de {{ meta.total }}</p>
        <div class="join">
            <template v-for="link in meta.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="join-item btn btn-sm"
                    :class="{ 'btn-active btn-primary': link.active }"
                    v-html="link.label"
                />
                <span v-else class="join-item btn btn-sm btn-disabled" v-html="link.label" />
            </template>
        </div>
    </nav>
</template>

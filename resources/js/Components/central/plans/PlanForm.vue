<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import { FIELD_LIMITS, ROUTE_NAMES_CENTRAL } from '@/Constants';
import type { PlanPayload } from '@/Types/central/plan';

defineProps<{ form: InertiaForm<PlanPayload>; submitLabel: string }>();

defineEmits<{
    submit: [];
    validate: [path: string];
}>();
</script>

<template>
    <form class="max-w-2xl space-y-8" novalidate @submit.prevent="$emit('submit')">
        <section class="space-y-4 rounded-box bg-base-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-ink">Dados do plano</h2>

            <AppFormField label="Nome" :error="form.errors.name">
                <AppTextInput
                    v-model="form.name"
                    :error="form.errors.name"
                    :maxlength="FIELD_LIMITS.NAME_MAX"
                    required
                    @blur="$emit('validate', 'name')"
                />
            </AppFormField>

            <AppFormField label="Preço mensal (R$)" :error="form.errors.price">
                <AppTextInput
                    v-model="form.price"
                    :error="form.errors.price"
                    type="number"
                    min="0"
                    step="0.01"
                    inputmode="decimal"
                    placeholder="0,00"
                    @blur="$emit('validate', 'price')"
                />
            </AppFormField>

            <label class="label cursor-pointer justify-start gap-3">
                <input v-model="form.is_active" type="checkbox" class="toggle toggle-primary" />
                <span class="label-text">Disponível para novas empresas</span>
            </label>
        </section>

        <section class="space-y-4 rounded-box bg-base-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-ink">Limites</h2>
            <p class="text-sm text-ink-muted">Deixe em branco para ilimitado. Os limites ainda não bloqueiam nada.</p>

            <div class="grid gap-x-4 sm:grid-cols-3">
                <AppFormField label="Usuários" :error="form.errors['limits.users']">
                    <AppTextInput
                        v-model="form.limits.users"
                        :error="form.errors['limits.users']"
                        type="number"
                        min="1"
                        step="1"
                        inputmode="numeric"
                        placeholder="Ilimitado"
                        @blur="$emit('validate', 'limits.users')"
                    />
                </AppFormField>
                <AppFormField label="Vistorias por mês" :error="form.errors['limits.trips_per_month']">
                    <AppTextInput
                        v-model="form.limits.trips_per_month"
                        :error="form.errors['limits.trips_per_month']"
                        type="number"
                        min="1"
                        step="1"
                        inputmode="numeric"
                        placeholder="Ilimitado"
                        @blur="$emit('validate', 'limits.trips_per_month')"
                    />
                </AppFormField>
                <AppFormField label="Armazenamento (MB)" :error="form.errors['limits.storage_mb']">
                    <AppTextInput
                        v-model="form.limits.storage_mb"
                        :error="form.errors['limits.storage_mb']"
                        type="number"
                        min="1"
                        step="1"
                        inputmode="numeric"
                        placeholder="Ilimitado"
                        @blur="$emit('validate', 'limits.storage_mb')"
                    />
                </AppFormField>
            </div>
        </section>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary rounded-full" :disabled="form.processing">
                <Icon icon="mdi:content-save-outline" class="size-5" />
                {{ submitLabel }}
            </button>
            <Link :href="route(ROUTE_NAMES_CENTRAL.PLANS.INDEX)" class="btn btn-ghost rounded-full">Cancelar</Link>
        </div>
    </form>
</template>

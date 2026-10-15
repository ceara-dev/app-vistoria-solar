<script setup lang="ts">
import { computed } from 'vue';
import type { InertiaForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppSelect from '@/Components/common/AppSelect.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import { FIELD_LIMITS, ROUTE_NAMES_CENTRAL } from '@/Constants';
import type { TenantPayload } from '@/Types/central/tenant';
import type { SelectOption } from '@/Types/shared';

const props = defineProps<{ form: InertiaForm<TenantPayload>; plans: SelectOption<number>[]; b2bClients: SelectOption<number>[] }>();

defineEmits<{
    submit: [];
    validate: [path: string];
}>();

const planOptions = computed<SelectOption<number | ''>[]>(() => [
    { value: '', label: 'Sem plano definido' },
    ...props.plans,
]);

const b2bClientOptions = computed<SelectOption<number | ''>[]>(() => [
    { value: '', label: 'Sem cliente vinculado' },
    ...props.b2bClients,
]);
</script>

<template>
    <form class="max-w-2xl space-y-8" novalidate @submit.prevent="$emit('submit')">
        <section class="space-y-4 rounded-box bg-base-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-ink">Dados da empresa</h2>

            <AppFormField label="Nome da empresa" :error="form.errors.name">
                <AppTextInput
                    v-model="form.name"
                    :error="form.errors.name"
                    :maxlength="FIELD_LIMITS.NAME_MAX"
                    required
                    @blur="$emit('validate', 'name')"
                />
            </AppFormField>

            <AppFormField label="E-mail do administrador" :error="form.errors.admin_email">
                <AppTextInput
                    v-model="form.admin_email"
                    :error="form.errors.admin_email"
                    :maxlength="FIELD_LIMITS.EMAIL_MAX"
                    type="email"
                    required
                    @blur="$emit('validate', 'admin_email')"
                />
                <p class="text-xs text-ink-muted">A pessoa recebe um convite para criar a conta e administrar a empresa.</p>
            </AppFormField>

            <AppFormField label="Cliente B2B" :error="form.errors.b2b_client_id">
                <AppSelect v-model="form.b2b_client_id" :options="b2bClientOptions" :error="form.errors.b2b_client_id" />
                <p class="text-xs text-ink-muted">Quem contratou a plataforma. Cadastre o cliente em "Clientes B2B".</p>
            </AppFormField>

            <AppFormField label="Plano" :error="form.errors.plan_id">
                <AppSelect v-model="form.plan_id" :options="planOptions" :error="form.errors.plan_id" />
            </AppFormField>
        </section>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary rounded-full" :disabled="form.processing">
                <Icon icon="mdi:plus" class="size-5" />
                Criar empresa
            </button>
            <Link :href="route(ROUTE_NAMES_CENTRAL.TENANTS.INDEX)" class="btn btn-ghost rounded-full">Cancelar</Link>
        </div>
    </form>
</template>

<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AppFormField from '@/Components/common/AppFormField.vue';
import AppSelect from '@/Components/common/AppSelect.vue';
import AppTextInput from '@/Components/common/AppTextInput.vue';
import { FIELD_LIMITS } from '@/Constants';
import type { InvitePayload, RoleOption } from '@/Types/tenant/member';

defineProps<{ form: InertiaForm<InvitePayload>; roles: RoleOption[] }>();

defineEmits<{ submit: []; validate: [field: keyof InvitePayload] }>();
</script>

<template>
    <form class="grid gap-4 rounded-box bg-base-100 p-6 shadow-sm md:grid-cols-[1fr_14rem_auto] md:items-end" novalidate @submit.prevent="$emit('submit')">
        <AppFormField label="E-mail" :error="form.errors.email">
            <AppTextInput
                v-model="form.email"
                icon="mdi:email-outline"
                :error="form.errors.email"
                :maxlength="FIELD_LIMITS.EMAIL_MAX"
                type="email"
                placeholder="pessoa@empresa.com.br"
                required
                @blur="$emit('validate', 'email')"
            />
        </AppFormField>

        <AppFormField label="Papel" :error="form.errors.role">
            <AppSelect v-model="form.role" :options="roles" :error="form.errors.role" required @change="$emit('validate', 'role')" />
        </AppFormField>

        <button type="submit" class="btn btn-primary h-12 rounded-full md:mb-1.5" :disabled="form.processing">
            <span v-if="form.processing" class="loading loading-spinner loading-sm" />
            <Icon v-else icon="mdi:send-outline" class="size-5" />
            Convidar
        </button>
    </form>
</template>

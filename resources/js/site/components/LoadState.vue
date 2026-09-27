<script setup>
import { useI18n } from 'vue-i18n';

defineProps({ loading: Boolean, error: [Object, Error, null], dark: Boolean });
defineEmits(['retry']);
const { t } = useI18n();
</script>

<template>
    <div v-if="loading" class="shell py-16" aria-busy="true">
        <div class="space-y-4">
            <div v-for="n in 4" :key="n" class="h-6 animate-pulse rounded" :class="dark ? 'bg-ivory/10' : 'bg-charcoal/[0.06]'" :style="{ width: `${90 - n * 12}%` }" />
        </div>
    </div>
    <div v-else-if="error" class="shell py-16">
        <p :class="dark ? 'text-ivory/80' : 'text-charcoal/80'">{{ t('errors.generic') }}</p>
        <button type="button" class="btn btn-ghost-dark mt-4" @click="$emit('retry')">↻</button>
    </div>
</template>

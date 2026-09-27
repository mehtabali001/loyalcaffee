<script setup>
import { computed, toRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSite } from '../stores/site';
import { formatClock, useOpenStatus } from '../composables/useTime';

defineProps({ compact: { type: Boolean, default: false } });
const site = useSite();
const { t, tm, rt } = useI18n();
const status = useOpenStatus(computed(() => site.settings.hours));

const label = computed(() => {
    const s = status.value;
    if (!s) return '';
    if (s.open) {
        const time = formatClock(s.closesAt, site.locale);
        return s.minutesLeft <= 45 ? t('status.closes_soon', { time }) : t('status.open_until', { time });
    }
    if (!s.opensAt) return t('status.closed');
    const time = formatClock(s.opensAt, site.locale);
    if (s.opensDay == null) return t('status.opens_at', { time });
    const days = tm('days');
    return t('status.opens_on', { day: rt(days[s.opensDay]), time });
});
</script>

<template>
    <p v-if="label" class="flex items-center gap-2 text-sm" role="status">
        <span class="relative flex h-2.5 w-2.5">
            <span v-if="status?.open" class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60 motion-reduce:hidden" style="background:#52b788" />
            <span class="relative inline-flex h-2.5 w-2.5 rounded-full" :style="{ background: status?.open ? '#52b788' : 'rgb(var(--c-terracotta-rgb))' }" />
        </span>
        <span :class="compact ? 'opacity-85' : ''">{{ label }}</span>
    </p>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSite } from '../stores/site';
import { cafeNow, formatClock } from '../composables/useTime';

defineProps({ dark: { type: Boolean, default: false } });
const site = useSite();
const { t, tm, rt } = useI18n();
const today = cafeNow().day;

// Collapse identical days ("Every day 8 AM – 3 AM") so the table stays short.
const rows = computed(() => {
    const hours = [...(site.settings.hours || [])].sort((a, b) => a.day - b.day);
    const same = hours.length === 7 && hours.every((h) => !h.closed && h.open === hours[0].open && h.close === hours[0].close);
    const range = (h) => (h.closed ? t('status.closed') : `${formatClock(h.open, site.locale)} – ${formatClock(h.close, site.locale)}`);
    if (same) return [{ label: t('status.every_day'), value: range(hours[0]), today: true }];
    const days = tm('days');
    return hours.map((h) => ({ label: rt(days[h.day]), value: range(h), today: h.day === today }));
});
</script>

<template>
    <dl class="space-y-2 tabular">
        <div v-for="r in rows" :key="r.label" class="flex justify-between gap-6" :class="r.today && rows.length > 1 ? 'font-semibold' : ''">
            <dt :class="dark ? 'text-ivory/70' : 'text-charcoal/70'">{{ r.label }}</dt>
            <dd dir="ltr" :class="$i18n.locale === 'ar' ? 'text-start' : ''">{{ r.value }}</dd>
        </div>
        <p v-if="site.settings.hours_note" class="pt-2 text-sm" :class="dark ? 'text-ivory/60' : 'text-charcoal/60'">{{ site.settings.hours_note }}</p>
    </dl>
</template>

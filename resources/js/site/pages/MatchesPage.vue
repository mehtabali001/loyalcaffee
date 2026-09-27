<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PageIntro from '../components/PageIntro.vue';
import MatchRow from '../components/MatchRow.vue';
import LoadState from '../components/LoadState.vue';
import { useSite } from '../stores/site';
import { useAsync } from '../composables/useAsync';
import { formatDate } from '../composables/useTime';

const site = useSite();
const { t, locale } = useI18n();
const { data, error, loading, run } = useAsync(() => site.load('events', '/events'));

// Group fixtures by week so a busy tournament stays scannable
const groups = computed(() => {
    const out = [];
    for (const e of data.value?.events || []) {
        const d = new Date(e.starts_at);
        const monday = new Date(d);
        monday.setDate(d.getDate() - ((d.getDay() + 6) % 7));
        const key = monday.toDateString();
        let g = out.find((x) => x.key === key);
        if (!g) out.push((g = { key, label: formatDate(monday.toISOString(), locale.value, { day: 'numeric', month: 'long' }), events: [] }));
        g.events.push(e);
    }
    return out;
});
</script>

<template>
    <div class="bg-emerald-night text-ivory">
        <PageIntro dark :title="t('matches.title')" :intro="t('matches.intro')" />
        <LoadState v-if="!data" dark :loading="loading" :error="error" @retry="run" />
        <div v-else class="shell pb-24">
            <p v-if="!groups.length" class="max-w-prose text-ivory/75">
                {{ t('matches.empty') }}
                <a v-if="site.whatsappLink()" :href="site.whatsappLink()" target="_blank" rel="noopener" class="text-gold underline">{{ t('cta.whatsapp') }}</a>
            </p>
            <section v-for="g in groups" :key="g.key" class="mb-12">
                <h2 class="mb-2 text-sm text-gold-soft">{{ g.label }}</h2>
                <div class="border-t border-ivory/10">
                    <MatchRow v-for="e in g.events" :key="e.id" :event="e" />
                </div>
            </section>
        </div>
    </div>
</template>

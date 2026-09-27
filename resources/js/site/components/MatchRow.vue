<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSite } from '../stores/site';
import { formatDate } from '../composables/useTime';
import { flagEmoji } from '../composables/flags';

/** A fixture laid out like a stadium board: date block · teams · kick-off · booking. */
const props = defineProps({ event: { type: Object, required: true } });
const site = useSite();
const { t, locale } = useI18n();

const e = computed(() => props.event);
const start = computed(() => new Date(e.value.starts_at));
const live = computed(() => {
    const now = Date.now();
    return now >= start.value.getTime() && now < start.value.getTime() + 2 * 60 * 60 * 1000;
});
const dayNum = computed(() => formatDate(e.value.starts_at, locale.value, { day: 'numeric' }));
const month = computed(() => formatDate(e.value.starts_at, locale.value, { month: 'short' }));
const weekday = computed(() => formatDate(e.value.starts_at, locale.value, { weekday: 'long' }));
const time = computed(() => formatDate(e.value.starts_at, locale.value, { hour: 'numeric', minute: '2-digit' }));
const hasTeams = computed(() => e.value.home_team && e.value.away_team);
</script>

<template>
    <article class="grid grid-cols-[4.5rem_1fr] items-center gap-x-5 gap-y-4 border-b border-ivory/10 py-6 sm:grid-cols-[5.5rem_1fr_auto]">
        <div class="text-center">
            <p class="tabular font-display text-4xl leading-none text-gold">{{ dayNum }}</p>
            <p class="mt-1 text-sm text-ivory/70">{{ month }}</p>
        </div>

        <div class="min-w-0">
            <p class="mb-1 flex flex-wrap items-center gap-x-3 text-sm text-ivory/60">
                <span v-if="e.competition">{{ e.competition }}</span>
                <span v-if="e.stage" class="text-ivory/45">{{ e.stage }}</span>
                <span v-if="live" class="rounded-full bg-terracotta px-2 py-0.5 text-xs font-semibold text-ivory">{{ t('matches.live') }}</span>
                <span v-else-if="e.is_big_match" class="rounded-full border border-gold/60 px-2 py-0.5 text-xs text-gold">{{ t('matches.big_match') }}</span>
            </p>
            <h3 v-if="hasTeams" class="font-display text-xl leading-snug sm:text-2xl">
                <span class="me-1.5" aria-hidden="true">{{ flagEmoji(e.home_flag) }}</span>{{ e.home_team }}
                <span class="mx-2 text-base text-ivory/50">{{ t('matches.vs') }}</span>
                <span class="me-1.5" aria-hidden="true">{{ flagEmoji(e.away_flag) }}</span>{{ e.away_team }}
            </h3>
            <h3 v-else class="font-display text-xl sm:text-2xl">{{ e.title }}</h3>
            <p class="mt-1 text-sm text-ivory/70">
                {{ weekday }}, <span class="tabular" dir="ltr">{{ time }}</span> {{ t('matches.gst') }}
            </p>
            <p v-if="e.offer" class="mt-2 text-sm text-gold-soft">{{ t('matches.offer') }}: {{ e.offer }}</p>
        </div>

        <div class="col-span-2 sm:col-span-1">
            <router-link
                v-if="e.reservations_open"
                :to="{ path: site.lp('/reserve'), query: { match: e.id } }"
                class="btn btn-gold w-full sm:w-auto"
            >{{ t('cta.reserve_match') }}</router-link>
            <p v-else class="text-sm text-ivory/60">{{ e.is_full ? t('matches.full') : t('matches.closed') }}</p>
        </div>
    </article>
</template>

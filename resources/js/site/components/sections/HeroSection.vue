<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import SmokeLine from '../SmokeLine.vue';
import OpenStatus from '../OpenStatus.vue';
import Icon from '@/shared/Icon.vue';
import { useSite } from '../../stores/site';

/**
 * The hero adapts between the bright, airy sky-blue day cafe mood
 * and the ambient luxury evening terrace lounge mood.
 */
const props = defineProps({ section: { type: Object, required: true } });
const site = useSite();
const { t } = useI18n();

const night = computed(() => site.heroMood === 'night');
const img = computed(() => (night.value ? props.section.image_secondary || props.section.image : props.section.image || props.section.image_secondary));
const d = computed(() => props.section.data || {});
const rating = computed(() => site.settings.rating);
const words = computed(() => String(props.section.title || '').split(/\s+/).filter(Boolean));
const ticker = computed(() => t('home.marquee').split('·').map((s) => s.trim()).filter(Boolean));
</script>

<template>
    <section
        class="grain relative isolate overflow-hidden transition-colors duration-700"
        :class="night ? 'bg-slate-950 text-white' : 'bg-gradient-to-b from-sky-50/60 via-white to-sky-50/30 text-charcoal'"
        aria-labelledby="hero-title"
    >
        <!-- Night: full-bleed evening terrace photo under ambient navy/sky wash -->
        <transition enter-active-class="transition-opacity duration-1000" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-500" leave-to-class="opacity-0">
            <div v-if="night" class="absolute inset-0 -z-10 overflow-hidden">
                <div class="ken-burns absolute inset-0">
                    <img v-if="img" v-parallax="0.18" :src="img" alt="" class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/75 to-slate-950/30" />
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-950/30 to-transparent rtl:bg-gradient-to-l" />
                <div class="absolute -bottom-40 start-1/4 h-96 w-96 rounded-full bg-sky-500/20 blur-[130px]" />
                <div class="lattice absolute inset-0 opacity-[0.06]" />
            </div>
        </transition>

        <!-- Day: radiant sky-blue & gold luminous pools -->
        <div v-if="!night" class="pointer-events-none absolute -top-40 start-1/4 -z-10 h-[36rem] w-[36rem] rounded-full bg-sky-200/40 blur-[140px]" />
        <div v-if="!night" class="pointer-events-none absolute top-1/4 end-[-10%] -z-10 h-[38rem] w-[38rem] rounded-full bg-amber-100/40 blur-[130px]" />

        <div
            class="shell grid min-h-[94svh] items-end gap-10 pb-24 pt-[calc(var(--header-h)+2.5rem)]"
            :class="night ? '' : 'lg:grid-cols-[1.1fr_0.9fr] lg:items-center'"
        >
            <div class="max-w-3xl">
                <!-- Location & Status Badge -->
                <div class="enter mb-6 flex flex-wrap items-center gap-3" style="--i: 0">
                    <OpenStatus />
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-sky/20 bg-sky-50/80 px-3.5 py-1 text-xs font-semibold text-sky-700 shadow-sm backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full bg-sky-500 animate-pulse" />
                        Madinat Zayed · Abu Dhabi
                    </span>
                </div>

                <h1 id="hero-title" class="t-display">
                    <template v-for="(w, i) in words" :key="`${site.heroMood}-${i}`">
                        <span class="word-mask"><span :style="{ '--i': i }">{{ w }}</span></span>{{ i < words.length - 1 ? ' ' : '' }}
                    </template>
                </h1>
                
                <SmokeLine :key="site.heroMood" class="mt-2" :class="night ? 'text-sky-400' : 'text-sky-600'" />
                
                <p v-if="section.subtitle" class="enter t-lead mt-6 max-w-xl font-normal leading-relaxed" style="--i: 3" :class="night ? 'text-white/80' : 'text-slate-600'">
                    {{ section.subtitle }}
                </p>

                <div class="enter mt-10 flex flex-wrap items-center gap-4" style="--i: 4">
                    <router-link v-if="section.cta_label" :to="site.lp(section.cta_link || '/reserve')" class="btn btn-sky group">
                        <Icon name="coffee" :size="18" class="text-white/90" />
                        {{ section.cta_label }}
                        <Icon name="chevron" :size="18" class="transition-transform duration-300 group-hover:translate-x-1 rtl:rotate-180 rtl:group-hover:-translate-x-1" />
                    </router-link>
                    <router-link v-if="d.secondary_label" :to="site.lp(d.secondary_link || '/menu')" class="btn" :class="night ? 'btn-ghost-light' : 'btn-ghost-dark'">
                        {{ d.secondary_label }}
                    </router-link>
                </div>

                <!-- GCC Review & Mood Switcher Row -->
                <div class="enter mt-12 flex flex-wrap items-center gap-x-8 gap-y-4 text-sm" style="--i: 5">
                    <a
                        v-if="rating?.value" :href="rating.url || '#'" target="_blank" rel="noopener"
                        class="flex items-center gap-2.5 rounded-full border border-sky/15 bg-white/70 px-4 py-2 shadow-sm transition-all hover:border-sky/40 hover:bg-white"
                        :class="night ? 'border-white/15 bg-white/10 text-white' : 'text-slate-700'"
                    >
                        <div class="flex gap-0.5 text-amber-400">
                            <Icon v-for="n in 5" :key="n" name="star" :size="14" class="fill-current" />
                        </div>
                        <span class="font-semibold">{{ Number(rating.value).toFixed(1) }}</span>
                        <span class="opacity-60 text-xs">({{ rating.count }}+ GCC Reviews)</span>
                    </a>

                    <!-- Day / Night Mood Switcher -->
                    <div class="inline-flex rounded-full border p-1 shadow-sm backdrop-blur" :class="night ? 'border-white/20 bg-white/10' : 'border-sky/20 bg-white/80'" role="group" :aria-label="t('home.mood')">
                        <button
                            v-for="m in ['day', 'night']" :key="m" type="button" :aria-pressed="site.heroMood === m"
                            class="flex items-center gap-1.5 rounded-full px-4 py-1.5 text-xs font-semibold transition-all duration-300"
                            :class="site.heroMood === m ? 'bg-sky-500 text-white shadow-md shadow-sky-500/30' : 'opacity-70 hover:opacity-100'"
                            @click="site.heroMood = m"
                        >
                            <Icon :name="m === 'day' ? 'sun' : 'moon'" :size="15" />
                            {{ t(m === 'day' ? 'home.by_day' : 'home.by_night') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Day: photograph framed in modern arched window with sky-blue ambient aura -->
            <figure v-if="!night && img" class="relative mx-auto mb-6 w-full max-w-[19rem] sm:max-w-xs lg:mb-0 lg:max-w-md">
                <!-- Outer rotating celestial geometric ring -->
                <svg class="spin-slow pointer-events-none absolute -end-8 -top-8 h-32 w-32 text-sky-400/40 lg:-end-14 lg:-top-10 lg:h-44 lg:w-44" viewBox="0 0 100 100" fill="none" aria-hidden="true">
                    <circle cx="50" cy="50" r="48" stroke="currentColor" stroke-width=".6" stroke-dasharray="2 3" />
                    <path d="M50 14 86 50 50 86 14 50z M50 30 70 50 50 70 30 50z" stroke="currentColor" stroke-width=".6" />
                    <circle cx="50" cy="50" r="4" stroke="currentColor" stroke-width=".8" />
                </svg>

                <div class="enter relative aspect-[4/5] rounded-t-full border-2 border-sky-300/60 p-2.5 shadow-2xl shadow-sky-500/15" style="--i: 2">
                    <div class="h-full w-full overflow-hidden rounded-t-full ring-1 ring-sky-200">
                        <div class="ken-burns h-full w-full">
                            <img :src="img" alt="Loyal Cafe & Lounge Luxury Interior" class="h-full w-full object-cover" fetchpriority="high">
                        </div>
                    </div>
                </div>

                <!-- Floating Specialty Tag -->
                <div class="float-slow enter absolute -bottom-6 -start-6 flex items-center gap-3.5 rounded-2xl border border-sky-100 bg-white/95 px-5 py-3.5 lg:-start-10 shadow-xl shadow-sky-900/10 backdrop-blur" style="--i: 6">
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-sky-400 to-sky-600 text-white shadow-md shadow-sky-500/30">
                        <Icon name="coffee" :size="22" />
                    </span>
                    <div>
                        <span class="block text-[0.7rem] uppercase tracking-wider text-sky-700 font-bold">Specialty Cafe</span>
                        <span class="font-display text-lg font-semibold leading-tight text-slate-900">{{ ticker[0] || 'Artisanal V60 & Spanish Latte' }}</span>
                    </div>
                </div>
            </figure>
        </div>

        <!-- Ticker: what Loyal is, at a glance -->
        <div v-if="ticker.length" class="marquee-host absolute inset-x-0 bottom-0 overflow-hidden border-t py-3.5 text-xs font-semibold tracking-[0.18em] backdrop-blur-md" :class="night ? 'border-white/10 bg-slate-900/60 text-sky-200' : 'border-sky-100 bg-white/70 text-sky-900'" aria-hidden="true">
            <div class="marquee">
                <template v-for="half in 2" :key="half">
                    <span v-for="(item, i) in [...ticker, ...ticker]" :key="`${half}-${i}`" class="flex shrink-0 items-center gap-6 pe-6 uppercase rtl:normal-case rtl:tracking-normal">
                        {{ item }} <span class="text-sky-400">✦</span>
                    </span>
                </template>
            </div>
        </div>
    </section>
</template>

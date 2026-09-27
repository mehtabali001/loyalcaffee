<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/shared/Icon.vue';
import { useSite } from '../../stores/site';

const props = defineProps({
    section: Object,
    testimonials: { type: Array, default: () => [] },
});
const site = useSite();
const { t } = useI18n();
const rating = computed(() => site.settings.rating || { value: 4.9, count: 180 });
</script>

<template>
    <section class="relative isolate overflow-hidden py-24 md:py-32 bg-gradient-to-b from-white via-sky-50/40 to-white">
        <!-- Ambient Sky Blue Background Glow -->
        <div class="pointer-events-none absolute -top-24 end-10 -z-10 h-96 w-96 rounded-full bg-sky-200/30 blur-[120px]" />
        <div class="pointer-events-none absolute -bottom-24 start-10 -z-10 h-96 w-96 rounded-full bg-sky-100/40 blur-[130px]" />

        <div class="shell">
            <!-- Header Row -->
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-8 mb-16">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span v-reveal="'rule'" class="gold-rule" />
                        <span class="text-xs font-bold uppercase tracking-widest text-sky-600 rtl:tracking-normal">
                            {{ t('home.reviews_badge', 'GCC Guest Experiences') }}
                        </span>
                    </div>
                    <h2 v-reveal class="t-h2 text-slate-900">{{ section.title || t('home.testimonials_title', 'What Our Guests Say') }}</h2>
                    <p class="mt-3 text-slate-600 max-w-xl text-base">
                        {{ t('home.testimonials_subtitle', 'Authentic impressions from our patrons across the UAE, Saudi Arabia, Qatar, and the wider Gulf region.') }}
                    </p>
                </div>

                <!-- Google Rating Badge -->
                <div v-if="rating.value" v-reveal="{ delay: 100 }" class="flex items-center gap-4 rounded-2xl border border-sky-100 bg-white p-4 shadow-lg shadow-sky-900/5 backdrop-blur shrink-0">
                    <div class="grid h-12 w-12 place-items-center rounded-xl bg-sky-50 text-sky-600">
                        <Icon name="google" :size="24" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="tabular font-display text-2xl font-bold text-slate-900">{{ Number(rating.value).toFixed(1) }}</span>
                            <div class="flex gap-0.5 text-amber-400">
                                <Icon v-for="n in 5" :key="n" name="star" :size="15" class="fill-current" />
                            </div>
                        </div>
                        <span class="text-xs text-slate-500 font-medium">{{ t('home.rating_verified', 'Verified Google Reviews') }} ({{ rating.count }}+)</span>
                    </div>
                </div>
            </div>

            <!-- Testimonials Grid -->
            <div v-if="testimonials.length" class="grid gap-6 md:grid-cols-2 lg:grid-cols-2">
                <article
                    v-for="(q, i) in testimonials.slice(0, 4)"
                    :key="q.id"
                    v-reveal="{ delay: i * 90 }"
                    class="glass-card flex flex-col justify-between p-7 sm:p-8"
                >
                    <div>
                        <!-- Top Row: Rating & Source -->
                        <div class="flex items-center justify-between gap-4 mb-5">
                            <div class="flex gap-1 text-amber-400">
                                <Icon v-for="n in (q.rating || 5)" :key="n" name="star" :size="16" class="fill-current" />
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 border border-sky-100 px-3 py-1 text-[0.75rem] font-semibold text-sky-700">
                                <svg class="h-3.5 w-3.5 text-sky-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                {{ t('reviews.verified_guest', 'Verified Guest') }}
                            </span>
                        </div>

                        <!-- Review Text -->
                        <blockquote class="font-display text-lg sm:text-xl font-medium leading-relaxed text-slate-800 mb-6">
                            “{{ q.content }}”
                        </blockquote>
                    </div>

                    <!-- Author Profile -->
                    <footer class="flex items-center gap-4 pt-5 border-t border-sky-100/80">
                        <div class="relative shrink-0">
                            <img
                                :src="q.avatar || `/images/reviews/${['tariq','reem','faisal','maryam'][i % 4]}.jpg`"
                                :alt="q.author"
                                class="h-14 w-14 rounded-full object-cover ring-2 ring-sky-300 shadow-md"
                                loading="lazy"
                            >
                            <span class="absolute -bottom-1 -end-1 grid h-5 w-5 place-items-center rounded-full bg-sky-500 text-white shadow-sm ring-2 ring-white">
                                <Icon name="check" :size="11" />
                            </span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <cite class="not-italic font-bold text-slate-900 text-base">{{ q.author }}</cite>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5 flex-wrap">
                                <span v-if="q.role" class="text-sky-700 font-medium">{{ q.role }}</span>
                                <span v-if="q.role && q.location" class="opacity-40">·</span>
                                <span v-if="q.location" class="flex items-center gap-1 text-slate-600">
                                    <Icon name="pin" :size="12" class="text-sky-500 shrink-0" />
                                    {{ q.location }}
                                </span>
                            </div>
                        </div>
                    </footer>
                </article>
            </div>

            <!-- Footer CTA -->
            <div class="mt-12 text-center" v-reveal="{ delay: 200 }">
                <a
                    v-if="rating.url"
                    :href="rating.url"
                    target="_blank"
                    rel="noopener"
                    class="btn btn-ghost-dark inline-flex items-center gap-2"
                >
                    <Icon name="google" :size="18" class="text-sky-600" />
                    {{ t('home.read_all_reviews', 'Read all 180+ reviews on Google Maps') }}
                </a>
            </div>
        </div>
    </section>
</template>

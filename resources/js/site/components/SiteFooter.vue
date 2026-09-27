<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/shared/Icon.vue';
import Logo from './Logo.vue';
import OpenStatus from './OpenStatus.vue';
import HoursTable from './HoursTable.vue';
import { useSite } from '../stores/site';

const site = useSite();
const { t, locale } = useI18n();
const s = computed(() => site.settings);
const rating = computed(() => s.value.rating || { value: 4.9, count: 180, url: 'https://maps.google.com' });

const socials = computed(() => {
    const raw = s.value.socials || {};
    const list = Object.entries(raw).filter(([, url]) => url);
    if (!list.length) {
        return [
            ['instagram', 'https://instagram.com'],
            ['tiktok', 'https://tiktok.com'],
            ['whatsapp', site.whatsappLink()],
            ['google', s.value.maps?.directions_url || 'https://maps.google.com'],
        ];
    }
    return list;
});

const year = new Date().getFullYear();

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>

<template>
    <footer class="relative overflow-hidden border-t border-sky-100 bg-gradient-to-b from-white via-sky-50/50 to-sky-100/40 text-slate-800">
        <!-- Ambient decorative sky glow -->
        <div class="pointer-events-none absolute -top-24 start-1/4 -z-10 h-72 w-72 rounded-full bg-sky-200/30 blur-[100px]" />
        <div class="pointer-events-none absolute -bottom-24 end-1/4 -z-10 h-80 w-80 rounded-full bg-sky-300/20 blur-[120px]" />

        <div class="shell relative grid gap-12 py-16 lg:grid-cols-[1.3fr_1fr_1fr_1fr] lg:gap-10">
            <!-- Col 1: Brand & Tagline & Google Review Badge & Socials -->
            <div class="space-y-6">
                <div>
                    <Logo />
                    <p class="mt-4 font-display text-xl font-bold text-sky-900 leading-snug">
                        {{ s.tagline || (locale === 'ar' ? 'خذ راحتك في ضيافتنا.' : 'Stay a while.') }}
                    </p>
                    <p class="mt-2 text-xs sm:text-sm text-slate-600 max-w-sm leading-relaxed">
                        {{ locale === 'ar' ? 'مقهى ولاونج خليجي راقي يقدم القهوة المختصة، الماتشا المنعشة، الأطباق المبتكرة والشيشة الملكية في مدينة زايد، أبوظبي.' : 'Specialty coffee, refreshing mocktails, artisanal dishes and luxury terrace shisha lounge in Madinat Zayed, Abu Dhabi.' }}
                    </p>
                </div>

                <!-- Google Review Interactive Card -->
                <a
                    :href="rating.url || s.maps?.directions_url || 'https://maps.google.com'"
                    target="_blank"
                    rel="noopener"
                    class="group inline-flex items-center gap-3.5 rounded-2xl border border-sky-200 bg-white/90 p-3.5 shadow-sm transition-all duration-300 hover:border-sky-400 hover:shadow-md hover:-translate-y-0.5"
                >
                    <div class="grid h-10 w-10 place-items-center rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white transition-colors">
                        <Icon name="google" :size="20" />
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="tabular font-display text-base font-bold text-slate-900">{{ Number(rating.value || 4.9).toFixed(1) }}</span>
                            <div class="flex gap-0.5 text-amber-400">
                                <Icon v-for="n in 5" :key="n" name="star" :size="13" class="fill-current" />
                            </div>
                        </div>
                        <span class="text-[0.72rem] font-semibold text-sky-700 underline group-hover:text-sky-800">
                            {{ locale === 'ar' ? 'قيّمنا على خرائط جوجل ✦' : 'Review us on Google Maps ✦' }}
                        </span>
                    </div>
                </a>

                <!-- Social Icons -->
                <div>
                    <p class="mb-2.5 text-xs font-bold uppercase tracking-wider text-slate-500">{{ t('footer.follow', 'Connect With Us') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <a
                            v-for="[name, url] in socials"
                            :key="name"
                            :href="url"
                            target="_blank"
                            rel="noopener"
                            class="grid h-10 w-10 place-items-center rounded-xl border border-sky-200 bg-white text-slate-700 shadow-sm transition-all duration-200 hover:border-sky-400 hover:bg-sky-500 hover:text-white hover:-translate-y-0.5"
                            :aria-label="name"
                        >
                            <Icon :name="name" :size="18" />
                        </a>
                    </div>
                </div>
            </div>

            <!-- Col 2: Quick Links -->
            <div>
                <h2 class="mb-4 text-xs font-bold uppercase tracking-wider text-sky-800">{{ t('footer.navigation', 'Explore') }}</h2>
                <ul class="space-y-2.5 text-sm font-medium text-slate-600">
                    <li>
                        <router-link :to="site.lp('/menu')" class="transition-colors hover:text-sky-600 hover:underline">
                            {{ t('nav.menu', 'Menu & Coffee') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link :to="site.lp('/reserve')" class="transition-colors hover:text-sky-600 hover:underline">
                            {{ t('nav.reserve', 'Table Reservation') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link :to="site.lp('/match-nights')" class="transition-colors hover:text-sky-600 hover:underline">
                            {{ t('nav.matches', 'Match Nights') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link :to="site.lp('/gallery')" class="transition-colors hover:text-sky-600 hover:underline">
                            {{ t('nav.gallery', 'Lounge Gallery') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link :to="site.lp('/contact')" class="transition-colors hover:text-sky-600 hover:underline">
                            {{ t('nav.contact', 'Location & Hours') }}
                        </router-link>
                    </li>
                </ul>
            </div>

            <!-- Col 3: Opening Hours -->
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-sky-800">{{ t('home.hours_title', 'Opening Hours') }}</h2>
                </div>
                <div class="rounded-2xl border border-sky-100 bg-white/80 p-4 shadow-sm">
                    <OpenStatus class="mb-3" compact />
                    <HoursTable />
                </div>
            </div>

            <!-- Col 4: Address & Contact -->
            <address class="not-italic space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-sky-800">{{ t('contact.address', 'Location & Contact') }}</h2>
                
                <div class="rounded-2xl border border-sky-100 bg-white/80 p-4 shadow-sm space-y-3 text-sm">
                    <div>
                        <p class="font-bold text-slate-900">{{ s.address?.street || 'Al Marami St 8' }}</p>
                        <p class="text-xs text-slate-500">{{ s.address?.area || 'Madinat Zayed, Abu Dhabi, UAE' }}</p>
                    </div>

                    <a
                        v-if="s.maps?.directions_url"
                        :href="s.maps.directions_url"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 hover:text-sky-700 underline"
                    >
                        <Icon name="pin" :size="15" class="text-sky-500" />
                        {{ t('cta.directions', 'Get Directions on Map') }}
                    </a>

                    <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                        <a v-if="site.telLink" :href="site.telLink" class="inline-flex items-center gap-2 font-bold text-slate-800 hover:text-sky-600" dir="ltr">
                            <Icon name="phone" :size="15" class="text-sky-500" />
                            {{ s.phone || '050 278 3288' }}
                        </a>
                        <a v-if="site.whatsappLink()" :href="site.whatsappLink()" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-xs font-bold text-emerald-600 hover:underline">
                            <Icon name="whatsapp" :size="15" class="text-emerald-500" />
                            {{ t('cta.whatsapp', 'WhatsApp Chat') }}
                        </a>
                    </div>
                </div>
            </address>
        </div>

        <!-- Bottom Copyright & Back to Top Bar -->
        <div class="shell relative flex flex-wrap items-center justify-between gap-4 border-t border-sky-200/60 py-6 text-xs text-slate-500">
            <p>© {{ year }} {{ s.site_name || 'Loyal Restaurant & Cafe' }}. {{ t('footer.rights', 'All rights reserved.') }}</p>

            <div class="flex items-center gap-6">
                <nav class="flex gap-4">
                    <router-link :to="site.lp('/menu')" class="hover:text-sky-600 hover:underline">{{ t('nav.menu') }}</router-link>
                    <router-link :to="site.lp('/reserve')" class="hover:text-sky-600 hover:underline">{{ t('nav.reserve') }}</router-link>
                    <router-link :to="site.lp('/contact')" class="hover:text-sky-600 hover:underline">{{ t('nav.contact') }}</router-link>
                </nav>

                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-white px-3 py-1 text-slate-700 shadow-sm transition-all hover:border-sky-400 hover:bg-sky-50 hover:text-sky-700"
                    @click="scrollToTop"
                >
                    <span>{{ locale === 'ar' ? 'للأعلى' : 'Top' }}</span>
                    <Icon name="chevron" :size="12" class="-rotate-90" />
                </button>
            </div>
        </div>
    </footer>
</template>

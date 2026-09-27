<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SectionHeading from '../SectionHeading.vue';
import Icon from '@/shared/Icon.vue';
import { useSite } from '../../stores/site';

const props = defineProps({
    section: Object,
    featured: { type: Array, default: () => [] },
});
const site = useSite();
const { t, locale } = useI18n();

const price = (v) => (v == null ? '' : new Intl.NumberFormat(locale.value === 'ar' ? 'ar-AE-u-nu-latn' : 'en-AE', { maximumFractionDigits: 2 }).format(v));

// Fallback image map if an item does not have a direct image
const fallbackImages = {
    'shisha': '/images/menu/double-apple.jpg',
    'coffee': '/images/menu/spanish-latte.jpg',
    'juice': '/images/menu/avocado-shake.jpg',
    'food': '/images/menu/avocado-toast.jpg',
    'dessert': '/images/menu/pistachio-cheesecake.jpg',
};
const getItemImage = (item) => {
    if (item.image) return item.image;
    const name = String(item.name || '').toLowerCase();
    if (name.includes('shisha') || name.includes('apple')) return '/images/menu/double-apple.jpg';
    if (name.includes('latte') || name.includes('coffee') || name.includes('v60')) return '/images/menu/spanish-latte.jpg';
    if (name.includes('avocado') && name.includes('shake')) return '/images/menu/avocado-shake.jpg';
    if (name.includes('avocado') && name.includes('cocktail')) return '/images/photos/specialty-drinks.jpg';
    if (name.includes('toast')) return '/images/menu/avocado-toast.jpg';
    if (name.includes('pistachio') || name.includes('cheesecake') || name.includes('kunafa')) return '/images/menu/pistachio-cheesecake.jpg';
    if (name.includes('box') || name.includes('sharing')) return '/images/menu/sharing-box.jpg';
    return '/images/photos/specialty-drinks.jpg';
};
</script>

<template>
    <section v-if="featured.length" class="relative overflow-hidden py-24 md:py-32 bg-gradient-to-b from-white via-sky-50/20 to-white">
        <!-- Ambient Sky Light Glow -->
        <div class="pointer-events-none absolute -top-32 start-1/2 -translate-x-1/2 -z-10 h-80 w-[42rem] rounded-full bg-sky-200/20 blur-[130px]" />

        <div class="shell">
            <SectionHeading
                :title="section.title"
                :subtitle="section.subtitle || (locale === 'ar' ? 'تشكيلة مختارة من أشهى أطباقنا والمشروبات والشيشة الملكية الأكثر طلباً' : 'Curated highlights of our most celebrated specialty brews, artisanal dishes, and signature shisha.')"
                :cta-label="section.cta_label"
                :cta-link="section.cta_link"
            />

            <!-- Visual Cards Grid with Images, Animated Fade, and Price Tags -->
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <article
                    v-for="(item, i) in featured"
                    :key="item.id"
                    v-reveal="{ delay: i * 80 }"
                    class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-sky-100 bg-white/90 shadow-lg shadow-sky-950/5 transition-all duration-300 hover:-translate-y-1.5 hover:border-sky-300 hover:shadow-xl hover:shadow-sky-500/15"
                >
                    <!-- Item Photo Container -->
                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-sky-50">
                        <img
                            :src="getItemImage(item)"
                            :alt="item.name"
                            loading="lazy"
                            class="h-full w-full object-cover transition-transform duration-700 ease-lounge group-hover:scale-108"
                        >
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-60 transition-opacity group-hover:opacity-40" />

                        <!-- Price Tag Badge -->
                        <div class="absolute top-3.5 end-3.5 z-10 flex items-center gap-1 rounded-full bg-slate-900/85 px-3.5 py-1.5 text-white shadow-lg backdrop-blur-md border border-white/20">
                            <span class="tabular font-display text-base font-bold text-sky-300">{{ price(item.price) }}</span>
                            <span class="text-[0.7rem] uppercase font-semibold text-slate-300">{{ t('menu.currency', 'AED') }}</span>
                        </div>

                        <!-- Tag Badges -->
                        <div v-if="item.tags?.length" class="absolute bottom-3 start-3.5 z-10 flex flex-wrap gap-1.5">
                            <span
                                v-for="tag in (item.tags || []).slice(0, 2)"
                                :key="tag"
                                class="rounded-full px-2.5 py-0.5 text-[0.7rem] font-bold uppercase tracking-wider backdrop-blur-md"
                                :class="tag === 'signature'
                                    ? 'bg-amber-400/90 text-slate-950 shadow-sm'
                                    : tag === 'new'
                                        ? 'bg-sky-400/90 text-white shadow-sm'
                                        : 'bg-white/90 text-slate-800 shadow-sm'"
                            >
                                {{ t(`tags.${tag}`, tag) }}
                            </span>
                        </div>
                    </div>

                    <!-- Item Content -->
                    <div class="flex flex-1 flex-col justify-between p-5 sm:p-6">
                        <div>
                            <h3 class="font-display text-lg sm:text-xl font-bold text-slate-900 leading-snug group-hover:text-sky-600 transition-colors">
                                {{ item.name }}
                            </h3>
                            <p v-if="item.description" class="mt-2 text-xs sm:text-sm leading-relaxed text-slate-500 line-clamp-2">
                                {{ item.description }}
                            </p>
                        </div>

                        <!-- Bottom Actions -->
                        <div class="mt-5 flex items-center justify-between gap-2 border-t border-slate-100 pt-3.5">
                            <router-link
                                :to="site.lp('/menu')"
                                class="inline-flex items-center gap-1 text-xs font-bold text-sky-600 hover:text-sky-800 transition-colors"
                            >
                                <span>{{ locale === 'ar' ? 'عرض' : 'View' }}</span>
                                <Icon name="chevron" :size="13" class="transition-transform group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5" />
                            </router-link>

                            <router-link
                                :to="site.lp('/reserve')"
                                class="inline-flex items-center justify-center rounded-full bg-sky-500 px-3.5 py-1 text-xs font-bold text-white shadow-sm shadow-sky-500/25 transition-all hover:bg-sky-600 hover:shadow-md hover:-translate-y-0.5"
                            >
                                {{ locale === 'ar' ? 'حجز' : 'Reserve' }}
                            </router-link>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Bottom Full Menu CTA -->
            <div class="mt-14 text-center" v-reveal="{ delay: 200 }">
                <router-link :to="site.lp('/menu')" class="btn btn-sky shadow-lg shadow-sky-500/20 px-8">
                    {{ section.cta_label || t('home.full_menu_btn', 'Explore Full Menu & Prices') }}
                    <Icon name="chevron" :size="18" class="rtl:rotate-180" />
                </router-link>
            </div>
        </div>
    </section>
</template>

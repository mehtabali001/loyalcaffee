<script setup>
import { useSite } from '../../stores/site';
import SectionHeading from '../SectionHeading.vue';
import Icon from '@/shared/Icon.vue';

defineProps({ section: Object });
const site = useSite();
</script>

<template>
    <section v-if="site.offers.length" class="bg-ivory-deep py-24">
        <div class="shell">
            <SectionHeading :title="section.title" :subtitle="section.subtitle" />
            <div class="grid gap-8 md:grid-cols-3">
                <div v-for="(o, i) in site.offers" :key="o.id" v-reveal="{ delay: i * 120 }">
                    <article class="lift group flex h-full flex-col overflow-hidden rounded-2xl bg-ivory ring-1 ring-charcoal/5">
                        <div v-if="o.image" class="relative aspect-[4/3] overflow-hidden">
                            <img :src="o.image" alt="" loading="lazy" class="h-full w-full object-cover transition-transform duration-[1.2s] ease-lounge group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-emerald-night/50 to-transparent" />
                            <p v-if="o.badge" class="absolute start-4 top-4 rounded-full bg-ivory/90 px-3 py-1 text-xs font-semibold text-terracotta backdrop-blur">{{ o.badge }}</p>
                        </div>
                        <div class="flex flex-1 flex-col border-t-2 border-gold p-6">
                            <p v-if="o.badge && !o.image" class="text-sm font-semibold text-terracotta">{{ o.badge }}</p>
                            <h3 class="t-h3">{{ o.title }}</h3>
                            <p class="mt-2 flex-1 text-charcoal/70">{{ o.description }}</p>
                            <router-link v-if="o.cta_link" :to="site.lp(o.cta_link)" class="mt-5 inline-flex items-center gap-1.5 font-semibold text-emerald">
                                {{ $t(o.cta_link.includes('reserve') ? 'cta.reserve' : o.cta_link.includes('menu') ? 'cta.view_menu' : 'cta.more') }}
                                <Icon name="chevron" :size="16" class="transition-transform duration-300 group-hover:translate-x-1 rtl:rotate-180 rtl:group-hover:-translate-x-1" />
                            </router-link>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>

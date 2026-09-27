<script setup>
import SectionHeading from '../SectionHeading.vue';
import { useSite } from '../../stores/site';

defineProps({ section: Object, gallery: { type: Array, default: () => [] } });
const site = useSite();
</script>

<template>
    <section v-if="gallery.length" class="py-24 md:py-28">
        <div class="shell">
            <SectionHeading :title="section.title" :subtitle="section.subtitle" :cta-label="section.cta_label" :cta-link="section.cta_link" />
            <!-- Editorial mosaic: first image leads, the rest follow at half size -->
            <div class="grid auto-rows-[11rem] grid-cols-2 gap-3 sm:auto-rows-[15rem] md:grid-cols-4">
                <router-link
                    v-for="(g, i) in gallery.slice(0, 5)" :key="g.id" v-reveal="{ as: 'curtain', delay: i * 110 }"
                    :to="site.lp('/gallery')"
                    class="group relative overflow-hidden rounded-xl bg-ivory-deep"
                    :class="i === 0 ? 'col-span-2 row-span-2' : ''"
                >
                    <img :src="g.image" :alt="g.alt || ''" loading="lazy" class="h-full w-full object-cover transition-transform duration-[1.2s] ease-lounge group-hover:scale-[1.07]">
                    <div class="absolute inset-0 bg-gradient-to-t from-emerald-night/80 via-emerald-night/0 to-transparent opacity-70 transition-opacity duration-500 group-hover:opacity-100" />
                    <p
                        v-if="g.caption"
                        class="absolute inset-x-0 bottom-0 translate-y-2 p-4 text-sm text-ivory opacity-0 transition-all duration-500 ease-lounge group-hover:translate-y-0 group-hover:opacity-100 sm:p-5"
                        :class="i === 0 ? 'font-display text-lg sm:text-2xl !opacity-100 !translate-y-0' : ''"
                    >{{ g.caption }}</p>
                </router-link>
            </div>
        </div>
    </section>
</template>

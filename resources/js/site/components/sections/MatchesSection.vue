<script setup>
import { useI18n } from 'vue-i18n';
import SectionHeading from '../SectionHeading.vue';
import MatchRow from '../MatchRow.vue';
import { useSite } from '../../stores/site';

defineProps({ section: Object, events: { type: Array, default: () => [] } });
const { t } = useI18n();
const site = useSite();
</script>

<template>
    <section class="grain relative isolate overflow-hidden bg-emerald-night py-24 text-ivory md:py-32">
        <div v-if="section.image" class="absolute inset-0 -z-20 overflow-hidden">
            <img v-parallax="0.15" :src="section.image" alt="" loading="lazy" class="h-full w-full object-cover opacity-30">
        </div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-emerald-night via-emerald-night/80 to-emerald-night" />
        <div class="lattice absolute inset-0 -z-10 opacity-[0.05]" />
        <div class="shell relative z-[2]">
            <SectionHeading dark :title="section.title" :subtitle="section.subtitle" :cta-label="events.length ? section.cta_label : null" :cta-link="section.cta_link" />
            <div v-if="events.length" class="border-t border-ivory/10">
                <div v-for="(e, i) in events" :key="e.id" v-reveal="{ delay: i * 90 }">
                    <MatchRow :event="e" />
                </div>
            </div>
            <p v-else v-reveal class="max-w-prose text-ivory/75">
                {{ t('home.no_matches') }}
                <a v-if="site.whatsappLink()" :href="site.whatsappLink()" target="_blank" rel="noopener" class="text-gold underline">{{ t('cta.whatsapp') }}</a>
            </p>
        </div>
    </section>
</template>

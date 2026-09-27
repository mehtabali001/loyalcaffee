<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/shared/Icon.vue';
import HoursTable from '../HoursTable.vue';
import MapEmbed from '../MapEmbed.vue';
import { useSite } from '../../stores/site';

defineProps({ section: Object });
const site = useSite();
const { t } = useI18n();
const s = computed(() => site.settings);
</script>

<template>
    <section class="bg-ivory-deep">
        <div class="grid lg:grid-cols-2">
            <div class="shell max-w-none py-20 lg:ps-[max(2rem,calc((100vw-76rem)/2+2rem))] lg:pe-16">
                <span v-reveal="'rule'" class="gold-rule mb-6" />
                <h2 v-reveal class="t-h2">{{ section.title }}</h2>
                <p v-if="section.subtitle" class="t-lead mt-3 text-charcoal/70">{{ section.subtitle }}</p>

                <address v-reveal="{ delay: 100 }" class="mt-10 not-italic">
                    <p class="text-lg">{{ s.address?.street }}<br>{{ s.address?.area }}</p>
                </address>

                <div v-reveal="{ delay: 180 }" class="mt-8 max-w-sm"><HoursTable /></div>

                <ul v-if="s.amenities?.length" v-reveal="{ delay: 240 }" class="mt-10 grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <li v-for="a in s.amenities" :key="a.icon + a.label" class="flex items-center gap-2.5 text-sm">
                        <Icon :name="a.icon" :size="20" class="shrink-0 text-emerald" /> {{ a.label }}
                    </li>
                </ul>

                <div class="mt-10 flex flex-wrap gap-3">
                    <a v-if="s.maps?.directions_url" :href="s.maps.directions_url" target="_blank" rel="noopener" class="btn btn-emerald">
                        <Icon name="pin" :size="18" /> {{ t('cta.directions') }}
                    </a>
                    <a v-if="site.telLink" :href="site.telLink" class="btn btn-ghost-dark"><Icon name="phone" :size="18" /> <span dir="ltr">{{ s.phone }}</span></a>
                </div>
            </div>
            <MapEmbed class="min-h-[24rem] lg:min-h-full" />
        </div>
    </section>
</template>

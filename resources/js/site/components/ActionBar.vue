<script setup>
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import Icon from '@/shared/Icon.vue';
import { useSite } from '../stores/site';

/** Proposal requirement: click-to-call + WhatsApp on every page. Mobile gets a thumb-reach bar; desktop a floating button. */
const site = useSite();
const route = useRoute();
const { t } = useI18n();
</script>

<template>
    <!-- Mobile -->
    <nav
        class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-3 border-t border-ivory/10 bg-emerald-night text-ivory md:hidden"
        :style="{ paddingBottom: 'env(safe-area-inset-bottom, 0px)' }" aria-label="Quick actions"
    >
        <a v-if="site.telLink" :href="site.telLink" class="flex flex-col items-center gap-1 py-2.5 text-xs">
            <Icon name="phone" /> {{ t('cta.call') }}
        </a>
        <a v-if="site.whatsappLink()" :href="site.whatsappLink()" target="_blank" rel="noopener" class="flex flex-col items-center gap-1 py-2.5 text-xs">
            <Icon name="whatsapp" /> WhatsApp
        </a>
        <router-link
            :to="site.lp('/reserve')" class="flex flex-col items-center gap-1 bg-gold py-2.5 text-xs font-semibold text-charcoal"
            :class="{ 'opacity-60': route.name === 'reserve' }"
        >
            <Icon name="calendar" /> {{ t('nav.reserve') }}
        </router-link>
    </nav>
    <div class="h-[calc(3.9rem+env(safe-area-inset-bottom,0px))] md:hidden" aria-hidden="true" />

    <!-- Desktop -->
    <a
        v-if="site.whatsappLink()" :href="site.whatsappLink()" target="_blank" rel="noopener"
        class="fixed bottom-6 end-6 z-30 hidden h-14 w-14 place-items-center rounded-full bg-[#25D366] text-white shadow-lg shadow-black/20 transition-transform hover:scale-105 md:grid"
        :aria-label="t('cta.whatsapp')"
    >
        <Icon name="whatsapp" :size="28" />
    </a>
</template>

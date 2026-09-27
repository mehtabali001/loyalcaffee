<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import SiteHeader from './components/SiteHeader.vue';
import SiteFooter from './components/SiteFooter.vue';
import ActionBar from './components/ActionBar.vue';
import { useSite } from './stores/site';
import { useHead } from './composables/useHead';

const site = useSite();
const { t } = useI18n();
useHead();

const announcement = computed(() => {
    const a = site.settings.announcement;
    return a?.enabled && a.text ? a : null;
});
</script>

<template>
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[60] focus:rounded focus:bg-gold focus:px-4 focus:py-2 focus:text-charcoal">
        {{ t('nav.skip') }}
    </a>

    <div v-if="announcement" class="relative z-50 bg-terracotta px-4 py-2 text-center text-sm text-ivory">
        <component :is="announcement.link ? 'a' : 'span'" :href="announcement.link ? site.lp(announcement.link) : undefined" class="hover:underline">
            {{ announcement.text }}
        </component>
    </div>

    <SiteHeader />

    <main id="main" tabindex="-1" class="outline-none">
        <router-view v-slot="{ Component, route }">
            <transition name="page" mode="out-in">
                <component :is="Component" :key="route.path" />
            </transition>
        </router-view>
    </main>

    <SiteFooter />
    <ActionBar />
</template>

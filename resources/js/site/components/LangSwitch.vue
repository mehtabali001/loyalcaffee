<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useSite } from '../stores/site';

const site = useSite();
const route = useRoute();
const NAMES = { en: 'English', ar: 'العربية' };

// Switching language is a full page load on purpose: fonts, text direction,
// hreflang/JSON-LD and cached content all change together and stay correct for crawlers.
const others = computed(() =>
    site.locales.filter((l) => l !== site.locale).map((l) => ({
        code: l,
        name: NAMES[l] || l.toUpperCase(),
        href: route.fullPath.replace(new RegExp(`^/${site.locale}(?=/|$|\\?)`), `/${l}`),
    })),
);
</script>

<template>
    <a
        v-for="o in others" :key="o.code" :href="o.href" :hreflang="o.code" :lang="o.code"
        class="rounded-full px-3 py-2 text-[0.95rem] font-medium opacity-85 transition-opacity hover:opacity-100"
        :style="{ fontFamily: o.code === 'ar' ? 'Cairo, sans-serif' : 'Montserrat, sans-serif' }"
    >{{ o.name }}</a>
</template>

<script setup>
import { useSite } from '../stores/site';

defineProps({ light: { type: Boolean, default: false } });
const site = useSite();
</script>

<template>
    <router-link :to="site.lp('/')" class="group flex items-center gap-3" :aria-label="site.settings.site_name">
        <img v-if="light && site.settings.logo_light" :src="site.settings.logo_light" :alt="site.settings.site_name" class="h-10 w-auto">
        <img v-else-if="!light && site.settings.logo" :src="site.settings.logo" :alt="site.settings.site_name" class="h-10 w-auto">
        <!-- Wordmark fallback until the real logo is uploaded -->
        <span v-else class="flex flex-col leading-none">
            <span class="font-display text-[1.65rem] font-semibold tracking-tight" style="font-family: 'Playfair Display', serif">Loyal</span>
            <span class="mt-1 text-[0.68rem] opacity-70" style="font-family: 'Cairo', sans-serif">مطعم وكافيه لويال</span>
        </span>
    </router-link>
</template>

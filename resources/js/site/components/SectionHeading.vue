<script setup>
import { useSite } from '../stores/site';

defineProps({
    title: String,
    subtitle: String,
    ctaLabel: String,
    ctaLink: String,
    dark: { type: Boolean, default: false },
});
const site = useSite();
</script>

<template>
    <div class="mb-12 flex flex-wrap items-end justify-between gap-x-10 gap-y-5">
        <div class="max-w-2xl">
            <span v-reveal="'rule'" class="gold-rule mb-5" />
            <h2 v-reveal class="t-h2">{{ title }}</h2>
            <p v-if="subtitle" v-reveal="{ delay: 120 }" class="t-lead mt-3" :class="dark ? 'text-ivory/75' : 'text-charcoal/70'">{{ subtitle }}</p>
        </div>
        <div v-if="ctaLabel && ctaLink" v-reveal="{ as: 'fade', delay: 200 }">
            <router-link :to="site.lp(ctaLink)" class="btn" :class="dark ? 'btn-ghost-light' : 'btn-ghost-dark'">
                {{ ctaLabel }}
            </router-link>
        </div>
    </div>
</template>

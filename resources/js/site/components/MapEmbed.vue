<script setup>
import { useSite } from '../stores/site';

const site = useSite();
</script>

<template>
    <div class="relative bg-emerald/10">
        <iframe
            v-if="site.settings.maps?.embed_url"
            :src="site.settings.maps.embed_url" :title="site.settings.site_name" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade" allowfullscreen
            class="absolute inset-0 h-full w-full border-0 grayscale-[35%] sepia-[15%]"
        />
    </div>
</template>

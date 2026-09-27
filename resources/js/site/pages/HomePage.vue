<script setup>
import { computed } from 'vue';
import { useSite } from '../stores/site';
import { useAsync } from '../composables/useAsync';
import LoadState from '../components/LoadState.vue';
import HeroSection from '../components/sections/HeroSection.vue';
import StorySection from '../components/sections/StorySection.vue';
import DayNightSection from '../components/sections/DayNightSection.vue';
import FeaturedMenuSection from '../components/sections/FeaturedMenuSection.vue';
import MatchesSection from '../components/sections/MatchesSection.vue';
import OffersSection from '../components/sections/OffersSection.vue';
import GallerySection from '../components/sections/GallerySection.vue';
import TestimonialsSection from '../components/sections/TestimonialsSection.vue';
import VisitSection from '../components/sections/VisitSection.vue';

/** The home page is assembled from Admin → Home sections, in the order and state set there. */
const REGISTRY = {
    hero: HeroSection,
    story: StorySection,
    day_night: DayNightSection,
    featured_menu: FeaturedMenuSection,
    matches: MatchesSection,
    offers: OffersSection,
    gallery: GallerySection,
    testimonials: TestimonialsSection,
    visit: VisitSection,
};

const site = useSite();
const { data, error, loading, run } = useAsync(() => site.load('home', '/home'));

const blocks = computed(() =>
    (data.value?.sections || [])
        .filter((s) => REGISTRY[s.type])
        .map((s) => ({ s, component: REGISTRY[s.type], props: { section: s, ...extras(s.type) } })),
);

// Each block only receives the data it declares, so nothing falls through as a DOM attribute.
function extras(type) {
    const d = data.value;
    return {
        featured_menu: { featured: d.featured },
        matches: { events: d.events },
        gallery: { gallery: d.gallery },
        testimonials: { testimonials: d.testimonials },
    }[type] || {};
}
</script>

<template>
    <div>
        <LoadState v-if="!data" :loading="loading" :error="error" class="pt-32" @retry="run" />
        <template v-else>
            <component :is="b.component" v-for="b in blocks" :key="b.s.id" v-bind="b.props" />
        </template>
    </div>
</template>

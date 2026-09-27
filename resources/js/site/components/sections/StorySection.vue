<script setup>
import { computed } from 'vue';

const props = defineProps({ section: { type: Object, required: true } });
const paragraphs = computed(() => (props.section.body || '').split(/\n{2,}/).filter(Boolean));
</script>

<template>
    <section class="relative overflow-hidden py-24 md:py-32">
        <div class="shell grid items-center gap-16 md:grid-cols-[0.8fr_1fr]">
            <figure v-if="section.image" class="relative order-last mx-auto w-full max-w-sm md:order-first">
                <!-- Offset gold arch behind the photograph -->
                <div v-reveal="{ as: 'fade', delay: 300 }" class="absolute inset-0 translate-x-5 translate-y-5 rounded-t-full border border-gold/60 rtl:-translate-x-5" aria-hidden="true" />
                <div v-reveal="'curtain'" class="relative aspect-[3/4] overflow-hidden rounded-t-full">
                    <img v-parallax="0.08" :src="section.image" alt="" loading="lazy" class="h-full w-full object-cover">
                </div>
            </figure>
            <div class="max-w-prose">
                <span v-reveal="'rule'" class="gold-rule mb-6" />
                <h2 v-reveal class="t-h2">{{ section.title }}</h2>
                <p
                    v-for="(p, i) in paragraphs" :key="i" v-reveal="{ delay: 120 * (i + 1) }"
                    class="mt-6 text-charcoal/80" :class="i === 0 ? 't-lead font-display text-charcoal' : ''"
                >
                    {{ p }}
                </p>
            </div>
        </div>
    </section>
</template>

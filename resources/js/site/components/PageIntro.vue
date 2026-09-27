<script setup>
import { computed } from 'vue';

/** Page header. With `image` it becomes a full-bleed photographic banner under an emerald wash. */
const props = defineProps({ title: String, intro: String, dark: { type: Boolean, default: false }, image: String, eyebrow: String });
const onDark = computed(() => props.dark || !!props.image);
const words = computed(() => String(props.title || '').split(/\s+/).filter(Boolean));
</script>

<template>
    <header
        class="relative isolate overflow-hidden pt-[calc(var(--header-h)+4rem)]"
        :class="[onDark ? 'bg-emerald-night text-ivory' : '', image ? 'grain pb-20 md:pb-24' : 'pb-14']"
    >
        <div v-if="image" class="absolute inset-0 -z-10 overflow-hidden">
            <div class="ken-burns absolute inset-0">
                <img v-parallax="0.2" :src="image" alt="" class="h-full w-full object-cover" fetchpriority="high">
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-emerald-night via-emerald-night/55 to-emerald-night/15" />
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-night/75 via-emerald-night/20 to-transparent rtl:bg-gradient-to-l" />
        </div>
        <div v-if="onDark" class="lattice absolute inset-0 -z-10 opacity-[0.05]" />

        <div class="shell relative z-[2]">
            <p v-if="eyebrow" class="enter mb-5 text-sm uppercase tracking-[0.22em] text-gold rtl:normal-case rtl:tracking-normal" style="--i: 0">{{ eyebrow }}</p>
            <span v-else class="enter gold-rule mb-6" style="--i: 0" />
            <h1 class="t-display !text-[clamp(2.6rem,6vw,4.75rem)]">
                <template v-for="(w, i) in words" :key="i">
                    <span class="word-mask"><span :style="{ '--i': i }">{{ w }}</span></span>{{ i < words.length - 1 ? ' ' : '' }}
                </template>
            </h1>
            <p v-if="intro" class="enter t-lead mt-5 max-w-2xl" style="--i: 3" :class="onDark ? 'text-ivory/80' : 'text-charcoal/70'">{{ intro }}</p>
            <div class="enter" style="--i: 4"><slot /></div>
        </div>
    </header>
</template>

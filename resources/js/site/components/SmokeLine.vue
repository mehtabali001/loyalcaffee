<script setup>
/**
 * The signature smoke-swirl motif (Brand Guide §1.12). Draws itself once on load —
 * the only ambient animation on the site. Mirrored automatically in RTL.
 */
defineProps({ animate: { type: Boolean, default: true } });
</script>

<template>
    <svg class="smoke rtl:-scale-x-100" viewBox="0 0 420 60" fill="none" aria-hidden="true" preserveAspectRatio="none">
        <path
            :class="{ draw: animate }"
            d="M2 42c38 0 46-28 84-28s40 34 80 34 44-40 88-40 36 26 70 26c22 0 30-14 44-22 14-8 26-6 40 2"
            stroke="currentColor" stroke-width="1.4" stroke-linecap="round" pathLength="1"
        />
    </svg>
</template>

<style scoped>
.smoke { display: block; width: min(26rem, 80%); height: 3.5rem; }
.draw { stroke-dasharray: 1; stroke-dashoffset: 1; animation: draw 2.4s .35s cubic-bezier(.45,.05,.2,1) forwards; }
@keyframes draw { to { stroke-dashoffset: 0; } }
@media (prefers-reduced-motion: reduce) { .draw { stroke-dashoffset: 0; animation: none; } }
</style>

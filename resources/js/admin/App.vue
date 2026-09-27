<script setup>
import { useRoute } from 'vue-router';
import AdminLayout from './components/AdminLayout.vue';
import { toasts } from './toast';

const route = useRoute();
</script>

<template>
    <RouterView v-if="route.meta.public" />
    <AdminLayout v-else />

    <div class="pointer-events-none fixed bottom-4 end-4 z-50 flex flex-col gap-2" aria-live="polite">
        <div
            v-for="t in toasts" :key="t.id"
            class="pointer-events-auto rounded-lg px-4 py-3 text-sm shadow-lg"
            :class="t.type === 'error' ? 'bg-terracotta text-white' : 'bg-emerald text-white'"
        >{{ t.message }}</div>
    </div>
</template>

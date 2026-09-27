<script setup>
import { ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Icon from '@/shared/Icon.vue';
import { useAuth } from '../stores/auth';
import { badges } from '../badges';

const auth = useAuth();
const route = useRoute();
const router = useRouter();
const open = ref(false);
watch(() => route.fullPath, () => (open.value = false));

const siteUrl = window.__LOYAL_ADMIN__?.appUrl || '/';
const nav = [
    { group: 'Today', items: [
        { to: '/', label: 'Dashboard', icon: 'star' },
        { to: '/reservations', label: 'Reservations', icon: 'calendar', badge: 'pending' },
        { to: '/messages', label: 'Messages', icon: 'whatsapp', badge: 'unread' },
    ] },
    { group: 'Content', items: [
        { to: '/r/sections', label: 'Homepage', icon: 'sun' },
        { to: '/r/menu-items', label: 'Menu items', icon: 'plate' },
        { to: '/r/menu-categories', label: 'Menu categories', icon: 'menu' },
        { to: '/r/gallery', label: 'Gallery', icon: 'search' },
        { to: '/r/events', label: 'Match nights', icon: 'football' },
        { to: '/r/offers', label: 'Offers', icon: 'glass' },
        { to: '/r/testimonials', label: 'Reviews', icon: 'google' },
    ] },
    { group: 'Setup', items: [
        { to: '/settings', label: 'Settings', icon: 'clock' },
        { to: '/translations', label: 'Website text', icon: 'globe' },
        { to: '/users', label: 'Staff accounts', icon: 'users', owner: true },
    ] },
];

async function logout() {
    await auth.logout();
    router.push('/login');
}
const isActive = (to) => (to === '/' ? route.path === '/' : route.path.startsWith(to));
</script>

<template>
    <div class="min-h-screen lg:grid lg:grid-cols-[15rem_1fr]">
        <!-- Mobile top bar -->
        <header class="sticky top-0 z-30 flex items-center justify-between bg-emerald-night px-4 py-3 text-white lg:hidden">
            <span class="font-display text-lg">Loyal <span class="text-gold">Admin</span></span>
            <button type="button" aria-label="Menu" @click="open = !open"><Icon :name="open ? 'close' : 'menu'" /></button>
        </header>

        <aside
            class="fixed inset-y-0 start-0 z-40 flex w-60 flex-col bg-emerald-night text-white/80 transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            :class="open ? 'translate-x-0' : '-translate-x-full rtl:translate-x-full'"
        >
            <div class="px-5 py-6">
                <p class="font-display text-xl text-white">Loyal</p>
                <p class="text-xs text-gold">Restaurant &amp; Cafe · CMS</p>
            </div>
            <nav class="flex-1 space-y-6 overflow-y-auto px-3 pb-6">
                <div v-for="g in nav" :key="g.group">
                    <p class="mb-1 px-3 text-[11px] font-semibold text-white/40">{{ g.group }}</p>
                    <template v-for="i in g.items" :key="i.to">
                        <RouterLink
                            v-if="!i.owner || auth.isOwner" :to="i.to"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition"
                            :class="isActive(i.to) ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white'"
                        >
                            <Icon :name="i.icon" :size="18" class="text-gold" />
                            <span class="flex-1">{{ i.label }}</span>
                            <span v-if="i.badge && badges[i.badge]" class="a-pill bg-gold text-charcoal">{{ badges[i.badge] }}</span>
                        </RouterLink>
                    </template>
                </div>
            </nav>
            <div class="border-t border-white/10 p-4 text-sm">
                <a :href="siteUrl" target="_blank" rel="noopener" class="mb-3 flex items-center gap-2 text-gold hover:underline"><Icon name="globe" :size="16" /> View website</a>
                <RouterLink to="/profile" class="block truncate text-white hover:underline">{{ auth.user?.name }}</RouterLink>
                <p class="truncate text-xs text-white/50">{{ auth.user?.email }}</p>
                <button type="button" class="mt-3 text-xs text-white/60 hover:text-white" @click="logout">Sign out</button>
            </div>
        </aside>
        <div v-if="open" class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="open = false" />

        <main class="min-w-0 px-4 py-6 md:px-8 md:py-8">
            <RouterView :key="route.path" />
        </main>
    </div>
</template>

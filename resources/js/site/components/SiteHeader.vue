<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import Logo from './Logo.vue';
import LangSwitch from './LangSwitch.vue';
import OpenStatus from './OpenStatus.vue';
import Icon from '@/shared/Icon.vue';
import { useSite } from '../stores/site';

const site = useSite();
const route = useRoute();
const { t } = useI18n();

const scrolled = ref(false);
const open = ref(false);
const onScroll = () => (scrolled.value = window.scrollY > 24);
onMounted(() => { onScroll(); window.addEventListener('scroll', onScroll, { passive: true }); });
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));
watch(() => route.fullPath, () => (open.value = false));
watch(open, (v) => (document.body.style.overflow = v ? 'hidden' : ''));

// Over the home hero the header is transparent and light; everywhere else it sits on ivory.
const overHero = computed(() => route.name === 'home' && site.heroMood === 'night' && !scrolled.value && !open.value);

const links = computed(() => [
    { to: '/menu', label: t('nav.menu') },
    { to: '/match-nights', label: t('nav.matches') },
    { to: '/gallery', label: t('nav.gallery') },
    { to: '/contact', label: t('nav.contact') },
]);
</script>

<template>
    <header
        class="fixed inset-x-0 top-0 z-40 transition-colors duration-300"
        :class="overHero ? 'bg-transparent text-ivory' : 'bg-ivory/95 text-charcoal shadow-[0_1px_0_rgb(var(--c-charcoal-rgb)/0.08)] backdrop-blur'"
        :style="{ paddingTop: 'env(safe-area-inset-top, 0px)' }"
    >
        <div class="shell flex h-[var(--header-h)] items-center justify-between gap-6">
            <Logo :light="overHero" />

            <nav class="hidden items-center gap-8 lg:flex" aria-label="Primary">
                <router-link
                    v-for="l in links" :key="l.to" :to="site.lp(l.to)"
                    class="relative py-2 text-[0.95rem] font-medium opacity-85 transition-opacity hover:opacity-100"
                    active-class="!opacity-100 after:absolute after:inset-x-0 after:-bottom-0.5 after:h-0.5 after:bg-sky-500 text-sky-600 font-semibold"
                >{{ l.label }}</router-link>
            </nav>

            <div class="flex items-center gap-3">
                <OpenStatus class="hidden xl:flex" compact />
                <LangSwitch />
                <router-link :to="site.lp('/reserve')" class="btn btn-sky hidden !py-2.5 sm:inline-flex">{{ t('cta.reserve') }}</router-link>
                <button
                    type="button" class="grid h-11 w-11 place-items-center rounded-full lg:hidden"
                    :aria-expanded="open" aria-controls="mobile-nav" :aria-label="open ? t('nav.close_menu') : t('nav.open_menu')"
                    @click="open = !open"
                >
                    <Icon :name="open ? 'close' : 'menu'" :size="26" />
                </button>
            </div>
        </div>

        <transition
            enter-active-class="transition duration-300 ease-lounge" enter-from-class="opacity-0 -translate-y-2"
            leave-active-class="transition duration-200" leave-to-class="opacity-0"
        >
            <nav v-if="open" id="mobile-nav" class="lattice-host fixed inset-x-0 bottom-0 top-[calc(var(--header-h)+env(safe-area-inset-top,0px))] overflow-y-auto bg-emerald-night text-ivory lg:hidden" aria-label="Mobile">
                <div class="lattice pointer-events-none absolute inset-0 opacity-[0.07]" />
                <div class="shell relative flex min-h-full flex-col py-10">
                    <router-link :to="site.lp('/')" class="py-3 font-display text-3xl">{{ t('nav.home') }}</router-link>
                    <router-link v-for="l in links" :key="l.to" :to="site.lp(l.to)" class="py-3 font-display text-3xl">{{ l.label }}</router-link>
                    <span class="gold-rule my-8" />
                    <OpenStatus class="mb-8" />
                    <router-link :to="site.lp('/reserve')" class="btn btn-gold w-full">{{ t('cta.reserve') }}</router-link>
                </div>
            </nav>
        </transition>
    </header>
</template>

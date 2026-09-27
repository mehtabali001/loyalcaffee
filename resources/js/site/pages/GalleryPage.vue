<script setup>
import { computed, nextTick, ref, watch, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/shared/Icon.vue';
import PageIntro from '../components/PageIntro.vue';
import LoadState from '../components/LoadState.vue';
import { useSite } from '../stores/site';
import { useAsync } from '../composables/useAsync';

const BANNER = '/images/photos/dallah-row.webp';

const site = useSite();
const { t } = useI18n();
const { data, error, loading, run } = useAsync(() => site.load('gallery', '/gallery'));

const filter = ref('all');
const items = computed(() => (data.value?.items || []).filter((i) => filter.value === 'all' || i.category === filter.value));
const cats = computed(() => ['all', ...new Set((data.value?.items || []).map((i) => i.category))]);
const countFor = (c) => (c === 'all' ? data.value?.items?.length : data.value?.items?.filter((i) => i.category === c).length) || 0;

// Lightbox
const current = ref(-1);
const dir = ref(1); // 1 = forward, -1 = back; drives the slide direction
let opener = null;
const closeBtn = ref(null);
const openAt = (i, e) => { opener = e?.currentTarget || null; dir.value = 1; current.value = i; };
const close = () => (current.value = -1);
const step = (d) => {
    dir.value = d;
    current.value = (current.value + d + items.value.length) % items.value.length;
};
const onKey = (e) => {
    if (current.value < 0) return;
    const rtl = site.rtl;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowRight') step(rtl ? -1 : 1);
    if (e.key === 'ArrowLeft') step(rtl ? 1 : -1);
};

// Swipe on phones: follow the reading direction, so a swipe towards the start goes forward.
let touchX = null;
const onTouchStart = (e) => (touchX = e.touches[0].clientX);
const onTouchEnd = (e) => {
    if (touchX == null) return;
    const dx = e.changedTouches[0].clientX - touchX;
    touchX = null;
    if (Math.abs(dx) < 50 || items.value.length < 2) return;
    const forward = dx < 0 ? 1 : -1;
    step(site.rtl ? -forward : forward);
};

// Warm the neighbours so stepping never waits on the network.
const preload = (i) => { if (items.value[i]) new Image().src = items.value[i].image; };

watch(current, (v, old) => {
    document.body.style.overflow = v >= 0 ? 'hidden' : '';
    if (v >= 0) {
        window.addEventListener('keydown', onKey);
        preload((v + 1) % items.value.length);
        preload((v - 1 + items.value.length) % items.value.length);
        if (old < 0) nextTick(() => closeBtn.value?.focus());
    } else {
        window.removeEventListener('keydown', onKey);
        opener?.focus?.();
    }
});
onBeforeUnmount(() => { window.removeEventListener('keydown', onKey); document.body.style.overflow = ''; });
</script>

<template>
    <div>
        <PageIntro :title="t('gallery.title')" :intro="t('gallery.intro')" :image="BANNER" :eyebrow="t('gallery.eyebrow')">
            <div v-if="cats.length > 2" class="mt-10 flex flex-wrap gap-2" role="tablist">
                <button
                    v-for="c in cats" :key="c" type="button" role="tab" :aria-selected="filter === c"
                    class="group inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm backdrop-blur transition-all duration-300 ease-lounge"
                    :class="filter === c ? 'border-gold bg-gold text-charcoal shadow-[0_10px_24px_-12px_rgb(var(--c-gold-rgb)/0.9)]' : 'border-ivory/25 bg-ivory/5 text-ivory hover:border-gold hover:text-gold'"
                    @click="filter = c"
                >
                    {{ t(`gallery.${c}`) }}
                    <span class="tabular text-xs opacity-60">{{ countFor(c) }}</span>
                </button>
            </div>
        </PageIntro>

        <LoadState v-if="!data" :loading="loading" :error="error" class="py-20" @retry="run" />
        <div v-else class="shell py-16 md:py-20">
            <p v-if="!items.length" class="text-charcoal/70">{{ t('gallery.empty') }}</p>
            <transition
                mode="out-in"
                enter-active-class="transition duration-500 ease-lounge" enter-from-class="opacity-0 translate-y-4"
                leave-active-class="transition duration-200" leave-to-class="opacity-0"
            >
                <div :key="filter" class="columns-1 gap-4 sm:columns-2 lg:columns-3">
                    <div v-for="(g, i) in items" :key="g.id" v-reveal="{ as: 'curtain', delay: (i % 3) * 110 }" class="mb-4 break-inside-avoid overflow-hidden rounded-xl">
                        <button
                            type="button"
                            class="group relative block w-full overflow-hidden text-start"
                            :aria-label="g.caption || t('gallery.view')"
                            @click="openAt(i, $event)"
                        >
                            <img :src="g.image" :alt="g.alt || ''" loading="lazy" class="w-full transition-transform duration-[1.2s] ease-lounge group-hover:scale-[1.06]">
                            <span class="absolute inset-0 bg-gradient-to-t from-emerald-night/85 via-emerald-night/10 to-transparent transition-opacity duration-500 [@media(hover:hover)]:opacity-0 [@media(hover:hover)]:group-hover:opacity-100" />
                            <span class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-4 p-5 text-ivory transition-all duration-500 ease-lounge [@media(hover:hover)]:translate-y-3 [@media(hover:hover)]:opacity-0 [@media(hover:hover)]:group-hover:translate-y-0 [@media(hover:hover)]:group-hover:opacity-100">
                                <span>
                                    <span class="block text-[0.7rem] uppercase tracking-[0.2em] text-gold-soft rtl:normal-case rtl:tracking-normal">{{ t(`gallery.${g.category}`) }}</span>
                                    <span v-if="g.caption" class="mt-1 block font-display text-lg leading-snug">{{ g.caption }}</span>
                                </span>
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-ivory/40 backdrop-blur"><Icon name="search" :size="18" /></span>
                            </span>
                        </button>
                    </div>
                </div>
            </transition>
        </div>

        <teleport to="body">
            <transition enter-active-class="transition duration-300 ease-lounge" enter-from-class="opacity-0" leave-active-class="transition duration-200" leave-to-class="opacity-0">
                <div
                    v-if="current >= 0"
                    class="fixed inset-0 z-[70] flex items-center justify-center bg-emerald-night/95 p-4 backdrop-blur-sm"
                    role="dialog" aria-modal="true" :aria-label="t('gallery.title')"
                    @click.self="close" @touchstart.passive="onTouchStart" @touchend="onTouchEnd"
                >
                    <div class="lattice pointer-events-none absolute inset-0 opacity-[0.04]" />
                    <transition
                        mode="out-in"
                        enter-active-class="transition duration-500 ease-lounge" :enter-from-class="dir > 0 ? 'opacity-0 translate-x-8 rtl:-translate-x-8 scale-[0.98]' : 'opacity-0 -translate-x-8 rtl:translate-x-8 scale-[0.98]'"
                        leave-active-class="transition duration-200" leave-to-class="opacity-0"
                    >
                        <figure :key="items[current].id" class="relative max-h-full max-w-5xl text-center" @click.self="close">
                            <img :src="items[current].image" :alt="items[current].alt || ''" class="mx-auto max-h-[78vh] rounded-xl object-contain shadow-[0_40px_80px_-30px_rgb(0_0_0/0.7)]">
                            <figcaption class="mt-5 text-ivory/85">
                                <span class="block text-xs uppercase tracking-[0.2em] text-gold rtl:normal-case rtl:tracking-normal">{{ t(`gallery.${items[current].category}`) }}</span>
                                <span v-if="items[current].caption" class="mt-1 block font-display text-xl">{{ items[current].caption }}</span>
                            </figcaption>
                        </figure>
                    </transition>
                    <p class="tabular absolute start-5 top-6 text-sm tracking-widest text-ivory/60" dir="ltr">{{ current + 1 }} / {{ items.length }}</p>
                    <button ref="closeBtn" type="button" class="absolute end-4 top-4 grid h-12 w-12 place-items-center rounded-full text-ivory transition-colors hover:bg-ivory/10" :aria-label="t('gallery.close')" @click="close"><Icon name="close" :size="26" /></button>
                    <button v-if="items.length > 1" type="button" class="absolute start-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-ivory/15 bg-emerald-night/40 text-ivory transition-colors hover:border-gold hover:text-gold sm:h-14 sm:w-14" :aria-label="t('gallery.prev')" @click="step(-1)"><Icon name="chevron" :size="26" class="rotate-180 rtl:rotate-0" /></button>
                    <button v-if="items.length > 1" type="button" class="absolute end-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-ivory/15 bg-emerald-night/40 text-ivory transition-colors hover:border-gold hover:text-gold sm:h-14 sm:w-14" :aria-label="t('gallery.next')" @click="step(1)"><Icon name="chevron" :size="26" class="rtl:rotate-180" /></button>
                </div>
            </transition>
        </teleport>
    </div>
</template>

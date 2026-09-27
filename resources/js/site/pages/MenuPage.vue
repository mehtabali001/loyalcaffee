<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/shared/Icon.vue';
import PageIntro from '../components/PageIntro.vue';
import MenuLine from '../components/MenuLine.vue';
import LoadState from '../components/LoadState.vue';
import { useSite } from '../stores/site';
import { useAsync } from '../composables/useAsync';

const site = useSite();
const { t } = useI18n();
const { data, error, loading, run } = useAsync(() => site.load('menu', '/menu'));

const q = ref('');
const active = ref(null);
const norm = (s) => (s || '').toLocaleLowerCase().normalize('NFKD').replace(/[\u064B-\u065F\u0670]/g, ''); // strip Arabic diacritics

const categories = computed(() => {
    const cats = data.value?.categories || [];
    const term = norm(q.value.trim());
    if (!term) return cats.filter((c) => c.items.length);
    return cats
        .map((c) => ({ ...c, items: c.items.filter((i) => norm(`${i.name} ${i.description}`).includes(term)) }))
        .filter((c) => c.items.length);
});

// Highlight the category currently in view in the sticky rail
let observer;
const watchSections = async () => {
    await nextTick();
    observer?.disconnect();
    observer = new IntersectionObserver(
        (entries) => entries.forEach((en) => en.isIntersecting && (active.value = en.target.id)),
        { rootMargin: '-35% 0px -60% 0px' },
    );
    document.querySelectorAll('[data-menu-cat]').forEach((el) => observer.observe(el));
};
onMounted(() => {
    const stop = setInterval(() => data.value && (clearInterval(stop), watchSections()), 50);
});
onBeforeUnmount(() => observer?.disconnect());

const jump = (slug) => {
    document.getElementById(`cat-${slug}`)?.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
};
</script>

<template>
    <div>
        <PageIntro :title="t('menu.title')" :intro="t('menu.intro')" />

        <LoadState v-if="!data" :loading="loading" :error="error" @retry="run" />

        <template v-else>
            <!-- Sticky category rail + search -->
            <div class="sticky top-[var(--header-h)] z-20 border-y border-charcoal/10 bg-ivory/95 backdrop-blur">
                <div class="shell flex items-center gap-4 py-3">
                    <nav class="-mx-2 flex flex-1 gap-1 overflow-x-auto px-2 [scrollbar-width:none]" aria-label="Menu categories">
                        <button
                            v-for="c in categories" :key="c.slug" type="button"
                            class="shrink-0 rounded-full px-4 py-2 text-sm transition-colors"
                            :class="active === `cat-${c.slug}` ? 'bg-emerald text-ivory' : 'hover:bg-charcoal/5'"
                            @click="jump(c.slug)"
                        >{{ c.name }}</button>
                    </nav>
                    <label class="relative hidden w-56 shrink-0 md:block">
                        <span class="sr-only">{{ t('menu.search') }}</span>
                        <Icon name="search" :size="18" class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 opacity-50" />
                        <input v-model="q" type="search" :placeholder="t('menu.search')" class="field !rounded-full !py-2 ps-10">
                    </label>
                </div>
                <div class="shell pb-3 md:hidden">
                    <input v-model="q" type="search" :placeholder="t('menu.search')" :aria-label="t('menu.search')" class="field !rounded-full !py-2">
                </div>
            </div>

            <div class="shell py-14">
                <p v-if="!categories.length" class="py-10 text-charcoal/70">{{ t('menu.no_results', { q }) }}</p>

                <section
                    v-for="c in categories" :id="`cat-${c.slug}`" :key="c.slug" data-menu-cat
                    class="grid gap-x-16 border-b border-charcoal/10 py-12 last:border-0 lg:grid-cols-[16rem_1fr]"
                >
                    <header class="mb-6 lg:sticky lg:top-[calc(var(--header-h)+6rem)] lg:mb-0 lg:self-start">
                        <Icon :name="c.icon || 'leaf'" :size="30" class="mb-4 text-terracotta" />
                        <h2 class="t-h2 !text-3xl">{{ c.name }}</h2>
                        <p v-if="c.description" class="mt-3 text-charcoal/65">{{ c.description }}</p>
                        <img v-if="c.image" :src="c.image" alt="" loading="lazy" class="mt-6 hidden aspect-[3/4] w-full rounded-t-full object-cover lg:block">
                    </header>
                    <div class="grid gap-x-12 xl:grid-cols-2">
                        <MenuLine v-for="item in c.items" :key="item.id" :item="item" />
                    </div>
                </section>
            </div>
        </template>
    </div>
</template>

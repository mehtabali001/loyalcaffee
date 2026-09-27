<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

/** One printed-menu line: name …… price, description below, tags as small marks. */
const props = defineProps({ item: { type: Object, required: true }, dark: { type: Boolean, default: false } });
const { t, locale } = useI18n();

const price = (v) => (v == null ? '' : new Intl.NumberFormat(locale.value === 'ar' ? 'ar-AE-u-nu-latn' : 'en-AE', { maximumFractionDigits: 2 }).format(v));
const shownTags = computed(() => (props.item.tags || []).filter((x) => x !== 'popular' || !props.item.tags.includes('signature')));
</script>

<template>
    <article class="group flex gap-4 py-4" :class="{ 'opacity-50': !item.is_available }">
        <img v-if="item.image" :src="item.image" :alt="item.name" loading="lazy" class="h-16 w-16 shrink-0 rounded-full object-cover">
        <div class="min-w-0 flex-1">
            <div class="leader" :class="dark ? '[&::after]:border-ivory/25' : ''">
                <h3 class="font-display text-lg leading-snug">{{ item.name }}</h3>
                <p v-if="item.price != null" class="tabular shrink-0 whitespace-nowrap font-semibold" :class="dark ? 'text-gold' : 'text-emerald'">
                    <s v-if="item.price_old" class="me-2 text-sm font-normal opacity-50">{{ price(item.price_old) }}</s>
                    {{ price(item.price) }}
                    <span class="text-xs font-normal opacity-70">{{ t('menu.currency') }}</span>
                </p>
            </div>
            <p v-if="item.description" class="mt-1 text-[0.95rem]" :class="dark ? 'text-ivory/65' : 'text-charcoal/65'">{{ item.description }}</p>
            <p v-if="shownTags.length || !item.is_available" class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs" :class="dark ? 'text-gold-soft' : 'text-terracotta'">
                <span v-if="!item.is_available">{{ t('menu.unavailable') }}</span>
                <span v-for="tag in shownTags" :key="tag">{{ t(`tags.${tag}`) }}</span>
            </p>
        </div>
    </article>
</template>

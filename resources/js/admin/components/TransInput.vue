<script setup>
/** One translatable value edited side by side: English left, Arabic right (RTL, Arabic font). */
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Object, default: () => ({}) },
    multiline: Boolean,
    rows: { type: Number, default: 3 },
    required: Boolean,
    errors: { type: Object, default: () => ({}) },
    field: String,
});
const emit = defineEmits(['update:modelValue']);
const locales = window.__LOYAL_ADMIN__?.locales || ['en', 'ar'];
const names = { en: 'English', ar: 'العربية' };
const val = computed(() => props.modelValue || {});
const set = (l, v) => emit('update:modelValue', { ...val.value, [l]: v });
</script>

<template>
    <div class="grid gap-3 md:grid-cols-2">
        <div v-for="(l, i) in locales" :key="l">
            <div class="mb-1 flex items-center justify-between text-[11px] text-stone-400">
                <span>{{ names[l] || l }}<span v-if="required && i === 0" class="text-terracotta"> *</span></span>
                <span v-if="!val[l] && i > 0 && val[locales[0]]" class="text-gold">missing</span>
            </div>
            <component
                :is="multiline ? 'textarea' : 'input'"
                :value="val[l] || ''" :rows="multiline ? rows : undefined"
                :dir="l === 'ar' ? 'rtl' : 'ltr'" :lang="l"
                class="a-input" :class="{ ar: l === 'ar' }"
                @input="set(l, $event.target.value)"
            />
            <p v-if="errors[`${field}.${l}`]" class="a-error">{{ errors[`${field}.${l}`] }}</p>
        </div>
    </div>
</template>

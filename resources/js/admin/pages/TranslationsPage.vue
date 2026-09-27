<script setup>
/**
 * Every fixed piece of website text (buttons, labels, form messages) ships in the code in
 * English and Arabic. Here staff can override any of them without a developer; clearing an
 * override falls back to the built-in wording.
 */
import { computed, reactive, ref } from 'vue';
import en from '@/site/i18n/en.json';
import ar from '@/site/i18n/ar.json';
import { api, errorMessage } from '../api';
import { toast } from '../toast';

function flatten(obj, prefix = '', out = {}) {
    for (const [k, v] of Object.entries(obj)) {
        const key = prefix ? `${prefix}.${k}` : k;
        v && typeof v === 'object' ? flatten(v, key, out) : (out[key] = v);
    }
    return out;
}
const bundled = { en: flatten(en), ar: flatten(ar) };
const keys = Object.keys(bundled.en);
const groups = [...new Set(keys.map((k) => k.split('.')[0]))];

const overrides = reactive({}); // key → {en, ar}
const touched = new Set();
const q = ref('');
const group = ref('');
const onlyChanged = ref(false);
const saving = ref(false);
const loaded = ref(false);

api.get('/translations').then(({ data }) => {
    for (const [k, v] of Object.entries(data || {})) overrides[k] = { ...v };
    loaded.value = true;
}).catch((e) => toast(errorMessage(e), 'error'));

const value = (k, l) => overrides[k]?.[l] ?? '';
function set(k, l, v) {
    overrides[k] = { ...(overrides[k] || {}), [l]: v };
    touched.add(k);
}
function reset(k) {
    overrides[k] = {};
    touched.add(k);
}
const isChanged = (k) => ['en', 'ar'].some((l) => value(k, l) && value(k, l) !== bundled[l][k]);

const list = computed(() => {
    const term = q.value.trim().toLowerCase();
    return keys.filter((k) => {
        if (group.value && !k.startsWith(`${group.value}.`)) return false;
        if (onlyChanged.value && !isChanged(k)) return false;
        if (!term) return true;
        return k.includes(term) || [bundled.en[k], bundled.ar[k], value(k, 'en'), value(k, 'ar')].some((s) => String(s || '').toLowerCase().includes(term));
    });
});

async function save() {
    saving.value = true;
    const payload = {};
    for (const k of touched) {
        const o = overrides[k] || {};
        // Store only real differences from the built-in text
        payload[k] = Object.fromEntries(['en', 'ar'].map((l) => [l, o[l] && o[l] !== bundled[l][k] ? o[l] : null]));
    }
    try {
        await api.put('/translations', { translations: payload });
        touched.clear();
        toast('Website text saved');
    } catch (e) {
        toast(errorMessage(e), 'error');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="max-w-6xl">
        <div class="mb-2 flex flex-wrap items-end justify-between gap-4">
            <h1 class="font-display text-3xl">Website text</h1>
            <button type="button" class="a-btn a-btn-primary" :disabled="saving" @click="save">{{ saving ? 'Saving…' : 'Save changes' }}</button>
        </div>
        <p class="mb-6 max-w-2xl text-sm text-stone-500">
            Buttons, labels and messages used across the site. Type in a box to replace the built-in wording; leave it empty to keep the default.
            Keep placeholders like <code class="rounded bg-stone-200 px-1">{time}</code> exactly as they are.
        </p>

        <div class="mb-4 flex flex-wrap items-center gap-3">
            <input v-model="q" type="search" placeholder="Search text…" class="a-input max-w-xs">
            <select v-model="group" class="a-input w-auto">
                <option value="">All areas</option>
                <option v-for="g in groups" :key="g" :value="g" class="capitalize">{{ g.replace('_', ' ') }}</option>
            </select>
            <label class="flex items-center gap-2 text-sm"><input v-model="onlyChanged" type="checkbox"> Only customised</label>
        </div>

        <div v-if="!loaded" class="text-sm text-stone-500">Loading…</div>
        <div v-else class="a-card divide-y divide-stone-100">
            <div v-for="k in list" :key="k" class="grid gap-3 p-4 md:grid-cols-[12rem_1fr_1fr]">
                <div class="text-xs">
                    <p class="break-all font-mono text-stone-500">{{ k }}</p>
                    <button v-if="isChanged(k)" type="button" class="mt-1 text-terracotta hover:underline" @click="reset(k)">Reset to default</button>
                </div>
                <div>
                    <input :value="value(k, 'en')" :placeholder="bundled.en[k]" class="a-input" :class="{ 'border-gold': value(k, 'en') }" dir="ltr" @input="set(k, 'en', $event.target.value)">
                </div>
                <div>
                    <input :value="value(k, 'ar')" :placeholder="bundled.ar[k]" class="a-input ar" :class="{ 'border-gold': value(k, 'ar') }" dir="rtl" lang="ar" @input="set(k, 'ar', $event.target.value)">
                </div>
            </div>
            <p v-if="!list.length" class="p-8 text-center text-sm text-stone-500">No text matches.</p>
        </div>
    </div>
</template>

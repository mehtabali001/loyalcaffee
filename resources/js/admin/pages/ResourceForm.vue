<script setup>
import { computed, ref } from 'vue';
import { onBeforeRouteLeave, useRouter } from 'vue-router';
import FormField from '../components/FormField.vue';
import { api, errorMessage, fieldErrors } from '../api';
import { resources, getPath, setPath } from '../resources';
import { invalidateOptions } from '../options';
import { toast } from '../toast';
import { tr, isoToLocalInput, localInputToServer } from '../format';

const props = defineProps({ resource: String, id: String });
const router = useRouter();
const cfg = computed(() => resources[props.resource]);
const isNew = computed(() => props.id === 'new');

const form = ref(null);
const errors = ref({});
const saving = ref(false);
let snapshot = '';

const visible = computed(() => (form.value ? cfg.value.fields.filter((f) => !f.hidden && (!f.show || f.show(form.value))) : []));
const dirty = computed(() => form.value && JSON.stringify(form.value) !== snapshot);

function blank() {
    const f = { ...(cfg.value.fixedFilters || {}) };
    for (const field of cfg.value.fields) {
        if (getPath(f, field.key) !== undefined) continue;
        const d = field.default ?? (field.type === 'toggle' ? false : field.type === 'tags' ? [] : field.type.startsWith('trans') ? {} : null);
        setPath(f, field.key, d);
    }
    return f;
}

async function load() {
    if (isNew.value) {
        form.value = blank();
    } else {
        try {
            const { data } = await api.get(`/${props.resource}/${props.id}`);
            // Fill any missing nested objects so v-model has somewhere to write
            const base = blank();
            for (const field of cfg.value.fields) {
                const v = getPath(data, field.key);
                if (v === undefined || v === null) setPath(data, field.key, getPath(base, field.key));
                if (field.type === 'datetime') setPath(data, field.key, isoToLocalInput(v));
            }
            form.value = data;
        } catch (e) {
            toast(errorMessage(e), 'error');
            router.replace(`/r/${props.resource}`);
            return;
        }
    }
    snapshot = JSON.stringify(form.value);
}
load();

function payload() {
    const out = { ...form.value };
    for (const field of cfg.value.fields) {
        if (field.type === 'datetime') setPath(out, field.key, localInputToServer(getPath(form.value, field.key)));
        if (field.type === 'number') {
            const v = getPath(form.value, field.key);
            setPath(out, field.key, v === '' || v == null ? null : Number(v));
        }
    }
    // Server-managed / presentational keys
    for (const k of Object.keys(out)) {
        if (k.endsWith('_url') || ['id', 'created_at', 'updated_at', 'sort_order', 'items_count', 'seats_taken'].includes(k)) delete out[k];
        else if (k === 'category' && out[k] && typeof out[k] === 'object') delete out[k]; // eager-loaded relation, not the gallery column
    }
    return out;
}

async function save(stay = false) {
    saving.value = true;
    errors.value = {};
    try {
        const { data } = isNew.value
            ? await api.post(`/${props.resource}`, payload())
            : await api.put(`/${props.resource}/${props.id}`, payload());
        if (props.resource === 'menu-categories') invalidateOptions('menu-categories');
        toast('Saved. The website updates immediately.');
        snapshot = JSON.stringify(form.value);
        if (isNew.value) router.replace(stay ? `/r/${props.resource}/${data.id}` : `/r/${props.resource}`);
        else if (!stay) router.push(`/r/${props.resource}`);
    } catch (e) {
        const f = fieldErrors(e);
        if (f) {
            errors.value = f;
            toast('Please check the highlighted fields.', 'error');
        } else toast(errorMessage(e), 'error');
    } finally {
        saving.value = false;
    }
}

onBeforeRouteLeave(() => (dirty.value && !saving.value ? confirm('Discard unsaved changes?') : true));
const heading = computed(() => (isNew.value ? `New ${cfg.value.singular}` : tr(form.value?.name || form.value?.title) || form.value?.author || `Edit ${cfg.value.singular}`));
const orphanErrors = computed(() => Object.entries(errors.value).filter(([k]) => !visible.value.some((f) => k === f.key || k.startsWith(`${f.key}.`))));
</script>

<template>
    <div class="max-w-4xl">
        <RouterLink :to="`/r/${resource}`" class="text-sm text-stone-500 hover:text-emerald">← {{ cfg.title }}</RouterLink>
        <h1 class="mb-6 mt-1 font-display text-3xl">{{ heading }}</h1>

        <div v-if="!form" class="text-sm text-stone-500">Loading…</div>
        <form v-else class="a-card grid gap-6 p-6 md:grid-cols-2" @submit.prevent="save(false)">
            <FormField v-for="f in visible" :key="f.key + f.label" :field="f" :form="form" :errors="errors" />

            <div v-if="orphanErrors.length" class="rounded-lg bg-terracotta/10 p-3 text-sm text-terracotta md:col-span-2">
                <p v-for="[k, m] in orphanErrors" :key="k">{{ m }}</p>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-stone-100 pt-5 md:col-span-2">
                <button type="submit" class="a-btn a-btn-primary" :disabled="saving">{{ saving ? 'Saving…' : 'Save' }}</button>
                <button type="button" class="a-btn a-btn-ghost" :disabled="saving" @click="save(true)">Save &amp; keep editing</button>
                <RouterLink :to="`/r/${resource}`" class="a-btn">Cancel</RouterLink>
            </div>
        </form>
    </div>
</template>

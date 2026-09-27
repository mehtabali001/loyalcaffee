<script setup>
/** Renders one schema field (see resources.js) bound to a nested path on the form object. */
import { computed } from 'vue';
import TransInput from './TransInput.vue';
import ImageField from './ImageField.vue';
import Toggle from './Toggle.vue';
import { getPath, setPath } from '../resources';
import { useOptions } from '../options';

const props = defineProps({ field: Object, form: Object, errors: { type: Object, default: () => ({}) } });
const f = computed(() => props.field);
const value = computed({
    get: () => getPath(props.form, f.value.key),
    set: (v) => setPath(props.form, f.value.key, v),
});
const error = computed(() => props.errors[f.value.key]);
const remote = f.value.type === 'remote' ? useOptions(f.value.source) : null;
const previewUrl = computed(() => (f.value.key.includes('.') ? null : props.form[`${f.value.key}_url`]));

function toggleTag(v) {
    const list = [...(value.value || [])];
    const i = list.indexOf(v);
    i > -1 ? list.splice(i, 1) : list.push(v);
    value.value = list;
}
</script>

<template>
    <div :class="field.half ? 'md:col-span-1' : 'md:col-span-2'">
        <template v-if="field.type === 'toggle'">
            <Toggle v-model="value" :label="field.label" />
        </template>
        <template v-else>
            <label class="a-label">{{ field.label }}<span v-if="field.required" class="text-terracotta"> *</span></label>

            <TransInput v-if="field.type === 'trans' || field.type === 'transText'" v-model="value" :multiline="field.type === 'transText'" :rows="field.rows" :required="field.required" :errors="errors" :field="field.key" />

            <ImageField v-else-if="field.type === 'image'" v-model="value" :preview-url="previewUrl" :folder="field.folder" />

            <select v-else-if="field.type === 'select'" v-model="value" class="a-input">
                <option v-if="field.empty || !field.required" :value="null">{{ field.empty || '—' }}</option>
                <option v-for="o in field.options" :key="o.value" :value="o.value">{{ o.label }}</option>
            </select>

            <select v-else-if="field.type === 'remote'" v-model="value" class="a-input">
                <option :value="null" disabled>Select…</option>
                <option v-for="o in remote.options.value" :key="o.value" :value="o.value">{{ o.label }}</option>
            </select>

            <div v-else-if="field.type === 'tags'" class="flex flex-wrap gap-2">
                <button
                    v-for="o in field.options" :key="o.value" type="button"
                    class="a-pill border px-3 py-1 capitalize"
                    :class="(value || []).includes(o.value) ? 'border-emerald bg-emerald text-white' : 'border-stone-300 bg-white text-stone-600'"
                    @click="toggleTag(o.value)"
                >{{ o.label }}</button>
            </div>

            <textarea v-else-if="field.type === 'textarea'" v-model="value" :rows="field.rows || 3" class="a-input" :dir="field.dir" />

            <input
                v-else
                v-model="value" class="a-input" :dir="field.dir || (['url', 'number', 'date', 'datetime'].includes(field.type) ? 'ltr' : undefined)"
                :type="{ number: 'number', date: 'date', datetime: 'datetime-local', url: 'text' }[field.type] || 'text'"
                :step="field.step" :placeholder="field.type === 'url' ? '/reserve' : field.placeholder"
            >

            <p v-if="field.help" class="a-hint">{{ field.help }}</p>
            <p v-if="error" class="a-error">{{ error }}</p>
        </template>
    </div>
</template>

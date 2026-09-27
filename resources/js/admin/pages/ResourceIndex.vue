<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Icon from '@/shared/Icon.vue';
import Toggle from '../components/Toggle.vue';
import { api, errorMessage, upload } from '../api';
import { resources, getPath } from '../resources';
import { useOptions } from '../options';
import { toast } from '../toast';
import { tr, fmtDateTime } from '../format';

const props = defineProps({ resource: String });
const route = useRoute();
const router = useRouter();
const cfg = computed(() => resources[props.resource]);

const rows = ref([]);
const loading = ref(true);
const meta = ref(null);
const q = ref(route.query.q || '');
const filters = reactive(Object.fromEntries((cfg.value.filters || []).map((f) => [f.key, route.query[f.key] ?? f.default ?? ''])));
const page = ref(Number(route.query.page) || 1);
const remoteFilters = Object.fromEntries((cfg.value.filters || []).filter((f) => f.type === 'remote').map((f) => [f.key, useOptions(f.source)]));

async function load() {
    loading.value = true;
    const params = { ...cfg.value.fixedFilters, q: q.value || undefined, page: cfg.value.paginated ? page.value : undefined };
    for (const [k, v] of Object.entries(filters)) if (v !== '' && v != null) params[k] = v;
    try {
        const { data } = await api.get(`/${props.resource}`, { params });
        rows.value = data.data;
        meta.value = cfg.value.paginated ? { current: data.current_page, last: data.last_page, total: data.total } : null;
    } catch (e) {
        toast(errorMessage(e), 'error');
    } finally {
        loading.value = false;
    }
}

let t;
watch([q, filters, page], () => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.replace({ query: { ...Object.fromEntries(Object.entries({ q: q.value, page: page.value > 1 ? page.value : '', ...filters }).filter(([, v]) => v !== '' && v != null)) } });
        load();
    }, 250);
});
watch([q, () => ({ ...filters })], () => (page.value = 1));
load();

/* ---------- Row actions ---------- */
async function toggle(row, field) {
    const prev = row[field];
    row[field] = !prev;
    try {
        await api.patch(`/${props.resource}/${row.id}/toggle`, { field });
    } catch (e) {
        row[field] = prev;
        toast(errorMessage(e), 'error');
    }
}
async function remove(row) {
    if (!confirm(`Delete this ${cfg.value.singular}? This can't be undone.`)) return;
    try {
        await api.delete(`/${props.resource}/${row.id}`);
        rows.value = rows.value.filter((r) => r.id !== row.id);
        toast('Deleted');
    } catch (e) {
        toast(errorMessage(e), 'error');
    }
}

/* ---------- Drag & drop ordering (only on the unfiltered list, so positions are unambiguous) ---------- */
const canSort = computed(() => cfg.value.sortable && !q.value && Object.values(filters).every((v) => !v));
const dragIndex = ref(null);
function onDragStart(i) { dragIndex.value = i; }
function onDragOver(i) {
    if (dragIndex.value === null || dragIndex.value === i) return;
    const list = [...rows.value];
    const [moved] = list.splice(dragIndex.value, 1);
    list.splice(i, 0, moved);
    rows.value = list;
    dragIndex.value = i;
}
async function onDrop() {
    dragIndex.value = null;
    try {
        await api.patch(`/${props.resource}/reorder`, { ids: rows.value.map((r) => r.id) });
        toast('Order saved');
    } catch (e) {
        toast(errorMessage(e), 'error');
        load();
    }
}
function move(i, dir) {
    const j = i + dir;
    if (j < 0 || j >= rows.value.length) return;
    const list = [...rows.value];
    [list[i], list[j]] = [list[j], list[i]];
    rows.value = list;
    onDrop();
}

/* ---------- Gallery multi-upload ---------- */
const bulkInput = ref(null);
const bulkProgress = ref(0);
async function bulkUpload(files) {
    if (!files?.length) return;
    bulkProgress.value = 1;
    try {
        const uploaded = await upload(files, 'gallery', (p) => (bulkProgress.value = Math.max(1, p)));
        await api.post('/gallery/bulk', { images: uploaded.map((f) => f.path), category: filters.category || 'interior' });
        toast(`${uploaded.length} photo${uploaded.length > 1 ? 's' : ''} added`);
        load();
    } catch (e) {
        toast(errorMessage(e), 'error');
    } finally {
        bulkProgress.value = 0;
        bulkInput.value.value = '';
    }
}

function cell(row, col) {
    const v = getPath(row, col.key);
    if (col.type === 'trans') return tr(v);
    if (col.type === 'datetime') return fmtDateTime(v);
    if (col.type === 'price') return v == null ? '—' : `AED ${Number(v).toFixed(v % 1 ? 2 : 0)}`;
    if (col.type === 'stars') return '★'.repeat(v || 0);
    if (col.type === 'seats') return `${v || 0}${row.capacity ? ` / ${row.capacity}` : ''}`;
    return v ?? '—';
}
</script>

<template>
    <div class="max-w-6xl">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl">{{ cfg.title }}</h1>
                <p v-if="canSort && rows.length > 1" class="text-sm text-stone-500">Drag rows to change the order on the website.</p>
            </div>
            <div class="flex gap-2">
                <template v-if="cfg.bulk">
                    <button type="button" class="a-btn a-btn-gold" :disabled="!!bulkProgress" @click="bulkInput.click()">
                        {{ bulkProgress ? `Uploading ${bulkProgress}%` : 'Upload photos' }}
                    </button>
                    <input ref="bulkInput" type="file" accept="image/*" multiple class="hidden" @change="bulkUpload($event.target.files)">
                </template>
                <RouterLink :to="`/r/${resource}/new`" class="a-btn a-btn-primary">Add {{ cfg.singular }}</RouterLink>
            </div>
        </div>

        <div class="mb-4 flex flex-wrap gap-3">
            <input v-if="resource !== 'sections'" v-model="q" type="search" placeholder="Search…" class="a-input max-w-xs">
            <template v-for="f in cfg.filters || []" :key="f.key">
                <select v-model="filters[f.key]" class="a-input w-auto">
                    <option value="">{{ f.label }}</option>
                    <option v-for="o in f.type === 'remote' ? remoteFilters[f.key].options.value : f.options" :key="o.value" :value="o.value">{{ o.label }}</option>
                </select>
            </template>
        </div>

        <div v-if="loading && !rows.length" class="text-sm text-stone-500">Loading…</div>
        <div v-else-if="!rows.length" class="a-card p-10 text-center text-sm text-stone-500">Nothing here yet.</div>

        <!-- Grid (gallery) -->
        <div v-else-if="cfg.grid" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            <div
                v-for="(row, i) in rows" :key="row.id"
                class="a-card group overflow-hidden" :class="{ 'opacity-50': !row.is_active, 'ring-2 ring-emerald': dragIndex === i }"
                :draggable="canSort" @dragstart="onDragStart(i)" @dragover.prevent="onDragOver(i)" @drop.prevent="onDrop" @dragend="dragIndex = null"
            >
                <RouterLink :to="`/r/${resource}/${row.id}`" class="block aspect-[4/3] bg-stone-100">
                    <img :src="row.image_url" alt="" class="h-full w-full object-cover" loading="lazy">
                </RouterLink>
                <div class="flex items-center justify-between gap-2 p-2.5 text-xs">
                    <span class="capitalize text-stone-600">{{ row.category?.replace('-', ' ') }}<span v-if="row.is_featured" class="text-gold"> · home</span></span>
                    <div class="flex items-center gap-2">
                        <Toggle :model-value="row.is_active" @update:model-value="toggle(row, 'is_active')" />
                        <button type="button" class="text-stone-400 hover:text-terracotta" aria-label="Delete" @click="remove(row)"><Icon name="close" :size="16" /></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div v-else class="a-card overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <th v-if="canSort" class="w-10" />
                        <th v-for="c in cfg.columns" :key="c.key">{{ c.label }}</th>
                        <th>Visible</th>
                        <th class="w-32" />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, i) in rows" :key="row.id"
                        :class="{ 'bg-emerald/5': dragIndex === i, 'text-stone-400': !row.is_active }"
                        :draggable="canSort" @dragstart="onDragStart(i)" @dragover.prevent="onDragOver(i)" @drop.prevent="onDrop" @dragend="dragIndex = null"
                    >
                        <td v-if="canSort" class="cursor-grab text-stone-400">
                            <div class="flex flex-col leading-none">
                                <button type="button" class="hover:text-emerald" aria-label="Move up" @click="move(i, -1)">▲</button>
                                <button type="button" class="hover:text-emerald" aria-label="Move down" @click="move(i, 1)">▼</button>
                            </div>
                        </td>
                        <td v-for="c in cfg.columns" :key="c.key">
                            <img v-if="c.type === 'thumb'" :src="row[c.key] || ''" alt="" class="h-10 w-14 rounded bg-stone-100 object-cover" :class="{ invisible: !row[c.key] }">
                            <Toggle v-else-if="c.type === 'toggle'" :model-value="row[c.key]" @update:model-value="toggle(row, c.key)" />
                            <RouterLink v-else-if="c === cfg.columns.find((x) => !['thumb', 'toggle'].includes(x.type))" :to="`/r/${resource}/${row.id}`" class="font-medium text-charcoal hover:text-emerald" :class="{ capitalize: c.key === 'type' }">
                                {{ String(cell(row, c)).replace('_', ' ') }}
                            </RouterLink>
                            <span v-else>{{ cell(row, c) }}</span>
                        </td>
                        <td><Toggle :model-value="row.is_active" @update:model-value="toggle(row, 'is_active')" /></td>
                        <td class="whitespace-nowrap text-end">
                            <RouterLink :to="`/r/${resource}/${row.id}`" class="a-btn a-btn-ghost a-btn-sm">Edit</RouterLink>
                            <button type="button" class="a-btn a-btn-danger a-btn-sm ms-1" @click="remove(row)">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="meta && meta.last > 1" class="mt-4 flex items-center justify-between text-sm text-stone-500">
            <span>{{ meta.total }} total</span>
            <div class="flex gap-2">
                <button class="a-btn a-btn-ghost a-btn-sm" :disabled="page <= 1" @click="page--">Previous</button>
                <span class="px-2 py-1">{{ meta.current }} / {{ meta.last }}</span>
                <button class="a-btn a-btn-ghost a-btn-sm" :disabled="page >= meta.last" @click="page++">Next</button>
            </div>
        </div>
    </div>
</template>

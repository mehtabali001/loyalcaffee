<script setup>
import { reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Icon from '@/shared/Icon.vue';
import StatusPill from '../components/StatusPill.vue';
import { api, errorMessage } from '../api';
import { refreshBadges } from '../badges';
import { toast } from '../toast';
import { tr, fmtDate, fmtDateTime } from '../format';

const STATUSES = ['pending', 'confirmed', 'seated', 'cancelled', 'no_show'];
const route = useRoute();
const router = useRouter();

const f = reactive({
    range: route.query.range ?? (route.query.status ? '' : 'upcoming'),
    status: route.query.status || '',
    date: route.query.date || '',
    q: route.query.q || '',
});
const page = ref(1);
const rows = ref([]);
const meta = ref({});
const loading = ref(true);
const openId = ref(null);

const params = () => Object.fromEntries(Object.entries({ ...f, page: page.value }).filter(([, v]) => v !== '' && v != null));

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/reservations', { params: params() });
        rows.value = data.data;
        meta.value = { current: data.current_page, last: data.last_page, total: data.total };
    } catch (e) {
        toast(errorMessage(e), 'error');
    } finally {
        loading.value = false;
    }
}
let t;
watch(() => ({ ...f }), () => {
    page.value = 1;
    clearTimeout(t);
    t = setTimeout(() => { router.replace({ query: params() }); load(); }, 250);
});
watch(page, load);
load();

async function update(row, patch, msg = 'Updated') {
    try {
        const { data } = await api.put(`/reservations/${row.id}`, patch);
        Object.assign(row, data);
        toast(msg);
        refreshBadges();
    } catch (e) {
        toast(errorMessage(e), 'error');
    }
}
/** Confirming opens a pre-written WhatsApp message to the guest in the guest's language. */
function confirmViaWhatsApp(row) {
    if (row.whatsapp_link) window.open(row.whatsapp_link, '_blank', 'noopener');
    if (row.status === 'pending') update(row, { status: 'confirmed' }, 'Marked as confirmed');
}
async function remove(row) {
    if (!confirm(`Delete booking ${row.reference}?`)) return;
    try {
        await api.delete(`/reservations/${row.id}`);
        rows.value = rows.value.filter((r) => r.id !== row.id);
        refreshBadges();
    } catch (e) {
        toast(errorMessage(e), 'error');
    }
}
async function exportCsv() {
    try {
        const { data } = await api.get('/reservations/export', { params: { ...params(), page: undefined }, responseType: 'blob' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(data);
        a.download = `reservations-${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
        setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    } catch (e) {
        toast(errorMessage(e), 'error');
    }
}
const seatingLabel = { indoor: 'Indoor', outdoor: 'Terrace', screens: 'Screens' };
</script>

<template>
    <div class="max-w-6xl">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl">Reservations</h1>
                <p class="text-sm text-stone-500">Confirm each request on WhatsApp. Unconfirmed past bookings become no-shows each morning.</p>
            </div>
            <button type="button" class="a-btn a-btn-ghost" @click="exportCsv">Export CSV</button>
        </div>

        <div class="mb-4 flex flex-wrap gap-3">
            <div class="flex overflow-hidden rounded-lg border border-stone-300 bg-white text-sm">
                <button v-for="r in [['today', 'Today'], ['upcoming', 'Upcoming'], ['past', 'Past'], ['', 'All']]" :key="r[0]" type="button"
                    class="px-3 py-2" :class="f.range === r[0] ? 'bg-emerald text-white' : 'hover:bg-stone-50'" @click="f.range = r[0]; f.date = ''">{{ r[1] }}</button>
            </div>
            <select v-model="f.status" class="a-input w-auto">
                <option value="">Any status</option>
                <option v-for="s in STATUSES" :key="s" :value="s" class="capitalize">{{ s.replace('_', ' ') }}</option>
            </select>
            <input v-model="f.date" type="date" class="a-input w-auto" @change="f.range = ''">
            <input v-model="f.q" type="search" placeholder="Name, phone or reference" class="a-input max-w-xs">
        </div>

        <div class="a-card overflow-x-auto">
            <table class="a-table">
                <thead><tr><th>When</th><th>Guest</th><th>Party</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    <template v-for="r in rows" :key="r.id">
                        <tr class="cursor-pointer hover:bg-stone-50" @click="openId = openId === r.id ? null : r.id">
                            <td class="whitespace-nowrap">
                                <p class="font-medium">{{ fmtDate(r.date) }}</p>
                                <p class="text-xs text-stone-500">{{ r.time }}</p>
                            </td>
                            <td>
                                <p class="font-medium">{{ r.name }}</p>
                                <p class="text-xs text-stone-500" dir="ltr">{{ r.phone }} · <span class="font-mono">{{ r.reference }}</span></p>
                            </td>
                            <td>
                                <p>{{ r.guests }} guests</p>
                                <p class="text-xs text-stone-500">
                                    {{ seatingLabel[r.seating] || 'Any seat' }}<template v-if="r.event"> · <Icon name="football" :size="12" class="inline" /> {{ tr(r.event.title) }}</template>
                                </p>
                            </td>
                            <td @click.stop>
                                <select :value="r.status" class="a-input w-32 py-1 text-xs capitalize" @change="update(r, { status: $event.target.value })">
                                    <option v-for="s in STATUSES" :key="s" :value="s">{{ s.replace('_', ' ') }}</option>
                                </select>
                            </td>
                            <td class="whitespace-nowrap text-end" @click.stop>
                                <button type="button" class="a-btn a-btn-primary a-btn-sm" @click="confirmViaWhatsApp(r)"><Icon name="whatsapp" :size="14" /> {{ r.status === 'pending' ? 'Confirm' : 'Message' }}</button>
                                <a :href="`tel:${r.phone}`" class="a-btn a-btn-ghost a-btn-sm ms-1"><Icon name="phone" :size="14" /></a>
                            </td>
                        </tr>
                        <tr v-if="openId === r.id" class="bg-stone-50">
                            <td colspan="5">
                                <div class="grid gap-4 py-2 md:grid-cols-3">
                                    <div class="text-sm">
                                        <p class="a-label">Guest notes</p>
                                        <p class="whitespace-pre-line">{{ r.notes || '—' }}</p>
                                        <p class="a-label mt-3">Contact</p>
                                        <p dir="ltr">{{ r.email || 'No email' }}</p>
                                        <p class="mt-3 text-xs text-stone-500">Requested {{ fmtDateTime(r.created_at) }} · <StatusPill :status="r.status" /></p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="a-label">Staff notes (private)</label>
                                        <textarea v-model="r.admin_notes" rows="3" class="a-input" />
                                        <div class="mt-2 flex gap-2">
                                            <button type="button" class="a-btn a-btn-ghost a-btn-sm" @click="update(r, { admin_notes: r.admin_notes }, 'Notes saved')">Save notes</button>
                                            <button type="button" class="a-btn a-btn-danger a-btn-sm" @click="remove(r)">Delete booking</button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr v-if="!loading && !rows.length"><td colspan="5" class="py-8 text-center text-stone-500">No reservations match these filters.</td></tr>
                </tbody>
            </table>
        </div>

        <div v-if="meta.last > 1" class="mt-4 flex items-center justify-between text-sm text-stone-500">
            <span>{{ meta.total }} bookings</span>
            <div class="flex gap-2">
                <button class="a-btn a-btn-ghost a-btn-sm" :disabled="page <= 1" @click="page--">Previous</button>
                <span class="px-2 py-1">{{ meta.current }} / {{ meta.last }}</span>
                <button class="a-btn a-btn-ghost a-btn-sm" :disabled="page >= meta.last" @click="page++">Next</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import Icon from '@/shared/Icon.vue';
import { api, errorMessage } from '../api';
import { refreshBadges } from '../badges';
import { toast } from '../toast';
import { fmtDateTime } from '../format';

const filter = ref('');
const q = ref('');
const page = ref(1);
const rows = ref([]);
const meta = ref({});
const openId = ref(null);

async function load() {
    try {
        const { data } = await api.get('/messages', { params: { filter: filter.value || undefined, q: q.value || undefined, page: page.value } });
        rows.value = data.data;
        meta.value = { current: data.current_page, last: data.last_page };
    } catch (e) {
        toast(errorMessage(e), 'error');
    }
}
let t;
watch([filter, q], () => { page.value = 1; clearTimeout(t); t = setTimeout(load, 250); });
watch(page, load);
load();

async function open(m) {
    openId.value = openId.value === m.id ? null : m.id;
    if (openId.value && !m.read_at) await toggleRead(m);
}
async function toggleRead(m) {
    const { data } = await api.patch(`/messages/${m.id}/read`);
    m.read_at = data.read_at;
    refreshBadges();
}
async function remove(m) {
    if (!confirm('Delete this message?')) return;
    await api.delete(`/messages/${m.id}`);
    rows.value = rows.value.filter((x) => x.id !== m.id);
    refreshBadges();
}
const wa = (phone) => `https://wa.me/${String(phone).replace(/\D/g, '').replace(/^0(5\d{8})$/, '971$1')}`;
</script>

<template>
    <div class="max-w-4xl">
        <h1 class="mb-6 font-display text-3xl">Messages</h1>
        <div class="mb-4 flex flex-wrap gap-3">
            <select v-model="filter" class="a-input w-auto">
                <option value="">All messages</option>
                <option value="unread">Unread</option>
            </select>
            <input v-model="q" type="search" placeholder="Search…" class="a-input max-w-xs">
        </div>

        <div class="a-card divide-y divide-stone-100">
            <div v-for="m in rows" :key="m.id">
                <button type="button" class="flex w-full items-start gap-3 px-5 py-4 text-start hover:bg-stone-50" @click="open(m)">
                    <span class="mt-2 h-2 w-2 shrink-0 rounded-full" :class="m.read_at ? 'bg-transparent' : 'bg-gold'" />
                    <span class="min-w-0 flex-1">
                        <span class="flex justify-between gap-4">
                            <span :class="m.read_at ? '' : 'font-semibold'">{{ m.name }}</span>
                            <span class="shrink-0 text-xs text-stone-500">{{ fmtDateTime(m.created_at) }}</span>
                        </span>
                        <span class="block truncate text-sm text-stone-500">{{ m.subject ? `${m.subject} · ` : '' }}{{ m.message }}</span>
                    </span>
                </button>
                <div v-if="openId === m.id" class="bg-stone-50 px-10 pb-5 pt-1 text-sm">
                    <p class="whitespace-pre-line" :dir="m.locale === 'ar' ? 'rtl' : 'ltr'">{{ m.message }}</p>
                    <p class="mt-3 text-xs text-stone-500" dir="ltr">{{ [m.phone, m.email].filter(Boolean).join(' · ') }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a v-if="m.phone" :href="wa(m.phone)" target="_blank" rel="noopener" class="a-btn a-btn-primary a-btn-sm"><Icon name="whatsapp" :size="14" /> Reply on WhatsApp</a>
                        <a v-if="m.email" :href="`mailto:${m.email}?subject=${encodeURIComponent('Re: ' + (m.subject || 'Loyal Cafe'))}`" class="a-btn a-btn-ghost a-btn-sm">Reply by email</a>
                        <button type="button" class="a-btn a-btn-ghost a-btn-sm" @click="toggleRead(m)">Mark {{ m.read_at ? 'unread' : 'read' }}</button>
                        <button type="button" class="a-btn a-btn-danger a-btn-sm" @click="remove(m)">Delete</button>
                    </div>
                </div>
            </div>
            <p v-if="!rows.length" class="p-8 text-center text-sm text-stone-500">No messages.</p>
        </div>
        <div v-if="meta.last > 1" class="mt-4 flex justify-end gap-2 text-sm">
            <button class="a-btn a-btn-ghost a-btn-sm" :disabled="page <= 1" @click="page--">Previous</button>
            <span class="px-2 py-1">{{ meta.current }} / {{ meta.last }}</span>
            <button class="a-btn a-btn-ghost a-btn-sm" :disabled="page >= meta.last" @click="page++">Next</button>
        </div>
    </div>
</template>

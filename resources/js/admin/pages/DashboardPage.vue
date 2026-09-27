<script setup>
import { computed, onMounted, ref } from 'vue';
import { refreshBadges } from '../badges';
import { tr, fmtDateTime, fmtDate } from '../format';
import StatusPill from '../components/StatusPill.vue';

const data = ref(null);
onMounted(async () => (data.value = await refreshBadges()));

const maxGuests = computed(() => Math.max(1, ...(data.value?.next_7_days || []).map((d) => d.guests)));
const dayName = (d) => new Date(`${d}T12:00:00Z`).toLocaleDateString('en-GB', { weekday: 'short' });
</script>

<template>
    <div class="max-w-6xl">
        <h1 class="font-display text-3xl">Good to see you</h1>
        <p class="mb-8 text-sm text-stone-500">Here's what's happening at Loyal.</p>

        <div v-if="!data" class="text-sm text-stone-500">Loading…</div>
        <template v-else>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <RouterLink to="/reservations?range=today" class="a-card p-5 hover:border-emerald">
                    <p class="text-xs text-stone-500">Bookings today</p>
                    <p class="mt-1 text-3xl font-semibold">{{ data.today.reservations }}</p>
                    <p class="text-xs text-stone-500">{{ data.today.guests }} guests</p>
                </RouterLink>
                <RouterLink to="/reservations?status=pending" class="a-card p-5 hover:border-emerald" :class="{ 'border-gold bg-gold/5': data.today.pending }">
                    <p class="text-xs text-stone-500">Waiting for confirmation</p>
                    <p class="mt-1 text-3xl font-semibold">{{ data.today.pending }}</p>
                    <p class="text-xs text-stone-500">Confirm on WhatsApp</p>
                </RouterLink>
                <RouterLink to="/messages" class="a-card p-5 hover:border-emerald">
                    <p class="text-xs text-stone-500">Unread messages</p>
                    <p class="mt-1 text-3xl font-semibold">{{ data.unread_messages }}</p>
                </RouterLink>
                <RouterLink to="/r/menu-items" class="a-card p-5 hover:border-emerald">
                    <p class="text-xs text-stone-500">Menu items</p>
                    <p class="mt-1 text-3xl font-semibold">{{ data.menu_items }}</p>
                </RouterLink>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <section class="a-card p-5">
                    <h2 class="mb-4 font-semibold">Guests booked, next 7 days</h2>
                    <div class="flex h-40 items-end gap-2">
                        <div v-for="d in data.next_7_days" :key="d.date" class="flex flex-1 flex-col items-center gap-1">
                            <span class="text-xs text-stone-500">{{ d.guests || '' }}</span>
                            <div class="w-full rounded-t bg-emerald/80" :style="{ height: `${(d.guests / maxGuests) * 100}%`, minHeight: '2px' }" />
                            <span class="text-xs text-stone-500">{{ dayName(d.date) }}</span>
                        </div>
                    </div>
                </section>

                <section class="a-card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-semibold">Upcoming match nights</h2>
                        <RouterLink to="/r/events/new" class="a-btn a-btn-ghost a-btn-sm">Add fixture</RouterLink>
                    </div>
                    <p v-if="!data.next_matches.length" class="text-sm text-stone-500">No upcoming fixtures. Add the next big games so guests can book.</p>
                    <ul class="divide-y divide-stone-100">
                        <li v-for="m in data.next_matches" :key="m.id" class="flex items-center justify-between gap-4 py-2.5 text-sm">
                            <RouterLink :to="`/r/events/${m.id}`" class="min-w-0 hover:text-emerald">
                                <p class="truncate font-medium">{{ m.home_team?.en && m.away_team?.en ? `${m.home_team.en} vs ${m.away_team.en}` : tr(m.title) }}</p>
                                <p class="text-xs text-stone-500">{{ fmtDateTime(m.starts_at) }}</p>
                            </RouterLink>
                            <span class="shrink-0 text-xs text-stone-500">{{ m.seats_taken || 0 }}{{ m.capacity ? ` / ${m.capacity}` : '' }} seats</span>
                        </li>
                    </ul>
                </section>
            </div>

            <section class="a-card mt-6 overflow-x-auto p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-semibold">Latest booking requests</h2>
                    <RouterLink to="/reservations" class="text-sm text-emerald hover:underline">All reservations</RouterLink>
                </div>
                <table class="a-table">
                    <thead><tr><th>Ref</th><th>Guest</th><th>When</th><th>Guests</th><th>Status</th></tr></thead>
                    <tbody>
                        <tr v-for="r in data.latest_reservations" :key="r.id">
                            <td class="font-mono text-xs">{{ r.reference }}</td>
                            <td>{{ r.name }}</td>
                            <td>{{ fmtDate(r.date) }} · {{ r.time }}</td>
                            <td>{{ r.guests }}</td>
                            <td><StatusPill :status="r.status" /></td>
                        </tr>
                        <tr v-if="!data.latest_reservations.length"><td colspan="5" class="text-stone-500">No bookings yet.</td></tr>
                    </tbody>
                </table>
            </section>
        </template>
    </div>
</template>

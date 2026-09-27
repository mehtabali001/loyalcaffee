<script setup>
import { computed, nextTick, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import Icon from '@/shared/Icon.vue';
import PageIntro from '../components/PageIntro.vue';
import HoursTable from '../components/HoursTable.vue';
import { useSite } from '../stores/site';
import { CAFE_TZ, formatClock, formatDate } from '../composables/useTime';
import { validationErrors } from '@/shared/http';

const site = useSite();
const route = useRoute();
const { t, locale } = useI18n();

const cfg = computed(() => site.settings.reservation || {});
const enabled = computed(() => cfg.value.enabled !== false);
const step = computed(() => Number(cfg.value.slot_minutes) || 30);
const lead = computed(() => Number(cfg.value.lead_minutes) || 30);

/* ---------- Dates in the cafe's timezone ---------- */
const ymd = (d) => new Intl.DateTimeFormat('en-CA', { timeZone: CAFE_TZ }).format(d); // YYYY-MM-DD
const addDays = (s, n) => {
    const d = new Date(`${s}T12:00:00Z`);
    d.setUTCDate(d.getUTCDate() + n);
    return d.toISOString().slice(0, 10);
};
const weekday = (s) => new Date(`${s}T12:00:00Z`).getUTCDay();
const toMin = (hhmm) => { const [h, m] = String(hhmm).split(':').map(Number); return h * 60 + m; };
const hhmm = (min) => `${String(Math.floor(min / 60) % 24).padStart(2, '0')}:${String(min % 60).padStart(2, '0')}`;
/** Minutes from now (cafe clock) until a given local date + time. */
const nowStamp = () => {
    const p = Object.fromEntries(new Intl.DateTimeFormat('en-GB', {
        timeZone: CAFE_TZ, year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
    }).formatToParts(new Date()).map((x) => [x.type, x.value]));
    return Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute);
};
const stamp = (date, time) => Date.parse(`${date}T${time}:00Z`);

const today = ymd(new Date());
const maxDate = addDays(today, 89);

/* ---------- Form ---------- */
const blank = () => ({ name: '', phone: '', email: '', guests: 2, date: today, slot: '', seating: '', event_id: '', notes: '', website: '' });
const form = reactive(blank());
const errors = ref({});
const sending = ref(false);
const failed = ref(false);
const done = ref(null);
const slotFilter = ref('all'); // all | morning | afternoon | evening | night

/**
 * The visitor picks the *visit day*; slots run through that day's shift, so a 01:00 slot on a
 * 08:00→03:00 shift is really the next calendar morning. Each slot therefore carries its own date.
 */
const shift = computed(() => (site.settings.hours || []).find((h) => h.day === weekday(form.date)));
const allSlots = computed(() => {
    const h = shift.value;
    if (!h || h.closed) return [];
    const open = toMin(h.open);
    let close = toMin(h.close);
    if (close <= open) close += 1440;
    const earliest = nowStamp() + lead.value * 60_000;
    const out = [];
    // Last seating one slot before closing
    for (let m = Math.ceil(open / step.value) * step.value; m <= close - step.value; m += step.value) {
        const date = m >= 1440 ? addDays(form.date, 1) : form.date;
        const time = hhmm(m);
        if (stamp(date, time) < earliest) continue;
        
        let period = 'evening';
        const mins = m % 1440;
        if (mins >= 480 && mins < 720) period = 'morning'; // 8am - 12pm
        else if (mins >= 720 && mins < 1020) period = 'afternoon'; // 12pm - 5pm
        else if (mins >= 1020 && mins < 1320) period = 'evening'; // 5pm - 10pm
        else period = 'night'; // 10pm - 3am

        const isPrime = (mins >= 1140 && mins <= 1380); // 7pm - 11pm

        out.push({
            value: `${date} ${time}`,
            date,
            time,
            late: m >= 1440,
            period,
            isPrime,
        });
    }
    return out;
});

const filteredSlots = computed(() => {
    if (slotFilter.value === 'all') return allSlots.value;
    return allSlots.value.filter((s) => s.period === slotFilter.value);
});

const closedDay = computed(() => shift.value?.closed === true);

watch(allSlots, (list) => {
    if (!list.some((s) => s.value === form.slot)) form.slot = list[0]?.value || '';
}, { immediate: true });

/* ---------- Match nights ---------- */
const events = ref([]);
site.load('events', '/events').then((d) => (events.value = (d.events || []).filter((e) => e.reservations_open))).catch(() => {});
const eventLabel = (e) => {
    const teams = e.home_team && e.away_team ? `${e.home_team} – ${e.away_team}` : e.title;
    return `${teams} · ${formatDate(e.starts_at, locale.value, { weekday: 'short', day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit' })}`;
};

/** Choosing a match moves the booking to half an hour before kick-off, facing the screens. */
function applyEvent(id) {
    const e = events.value.find((x) => String(x.id) === String(id));
    if (!e) return;
    const k = new Date(e.starts_at);
    const date = ymd(k);
    const [hh, mm] = new Intl.DateTimeFormat('en-GB', { timeZone: CAFE_TZ, hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }).format(k).split(':').map(Number);
    let m = Math.floor((hh * 60 + mm - 30) / step.value) * step.value;
    // An after-midnight kick-off belongs to the previous visit day
    let visit = date;
    if (m < toMin(shift.value?.open || '08:00') && hh < 6) { visit = addDays(date, -1); m += 1440; }
    form.date = visit;
    const want = `${m >= 1440 ? date : visit} ${hhmm(m)}`;
    setTimeout(() => { if (allSlots.value.some((s) => s.value === want)) form.slot = want; });
    if (!form.seating) form.seating = 'screens';
}
watch(() => form.event_id, (id) => id && applyEvent(id));
watch(events, () => { if (route.query.match) form.event_id = String(route.query.match); }, { once: true });

/* ---------- Submit ---------- */
async function submit() {
    errors.value = {};
    failed.value = false;
    sending.value = true;
    const s = allSlots.value.find((x) => x.value === form.slot);
    try {
        const { data } = await site.http().post('/reservations', {
            name: form.name.trim(),
            phone: form.phone.trim(),
            email: form.email.trim() || null,
            guests: Number(form.guests),
            date: s?.date || form.date,
            time: s?.time || '',
            seating: form.seating || null,
            event_id: form.event_id ? Number(form.event_id) : null,
            notes: form.notes.trim() || null,
            website: form.website,
        });
        done.value = data.reference;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (e) {
        const v = validationErrors(e);
        if (v) {
            if (v.date || v.time) v.slot = v.time || v.date;
            errors.value = v;
            nextTick(() => document.querySelector('form [aria-invalid="true"]')?.focus());
        } else failed.value = true;
    } finally {
        sending.value = false;
    }
}

function again() {
    Object.assign(form, blank());
    done.value = null;
}

const summaryText = computed(() => {
    if (!done.value) return '';
    return locale.value === 'ar'
        ? `مرحباً، أرسلت طلب حجز رقم ${done.value}.`
        : `Hi, I just sent booking request ${done.value}.`;
});

const guestOptions = computed(() => {
    const max = Math.min(Number(cfg.value.max_guests) || 30, 30);
    return Array.from({ length: max - (Number(cfg.value.min_guests) || 1) + 1 }, (_, i) => i + (Number(cfg.value.min_guests) || 1));
});
const minGuests = computed(() => guestOptions.value[0] || 1);
const maxGuests = computed(() => guestOptions.value[guestOptions.value.length - 1] || 30);
const bumpGuests = (d) => (form.guests = Math.min(maxGuests.value, Math.max(minGuests.value, Number(form.guests) + d)));

const seatingOptions = [
    { value: '', icon: 'users', label: 'reserve.seating_any', desc_en: 'Best available seating', desc_ar: 'أفضل جلسة متوفرة' },
    { value: 'indoor', icon: 'coffee', label: 'reserve.seating_indoor', desc_en: 'Sunlit marble lounge & AC', desc_ar: 'جلسات رخامية مشمسة ومكيفة' },
    { value: 'outdoor', icon: 'leaf', label: 'reserve.seating_outdoor', desc_en: 'Open sky terrace & shisha', desc_ar: 'تراس مفتوح تحت النجوم' },
    { value: 'screens', icon: 'screen', label: 'reserve.seating_screens', desc_en: 'Prime match viewing view', desc_ar: 'أمام الشاشات العملاقة' },
];

const selectedSlotObject = computed(() => allSlots.value.find((s) => s.value === form.slot));

const BANNER = '/images/photos/evening-terrace.jpg';
const SIDE_PHOTO = '/images/photos/hero-sky-luxury.jpg';
</script>

<template>
    <div class="bg-gradient-to-b from-sky-50/50 via-white to-sky-50/30">
        <PageIntro
            :title="t('reserve.title')"
            :intro="t('reserve.intro')"
            :image="BANNER"
            :eyebrow="t('reserve.eyebrow', 'GCC VIP Hospitality')"
        />

        <div class="relative">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-white/90 to-transparent" />
            
            <div class="shell relative grid gap-12 py-12 md:py-16 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-14">
                <transition
                    mode="out-in"
                    enter-active-class="transition duration-700 ease-lounge" enter-from-class="opacity-0 translate-y-6 scale-[0.98]"
                    leave-active-class="transition duration-200" leave-to-class="opacity-0"
                >
                    <!-- Success Screen -->
                    <section v-if="done" key="done" class="relative overflow-hidden rounded-3xl bg-slate-950 p-8 text-white sm:p-12 shadow-2xl shadow-sky-950/40" aria-live="polite">
                        <div class="lattice pointer-events-none absolute inset-0 opacity-[0.08]" />
                        <div class="relative">
                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-500/20 text-sky-400 border border-sky-400/30">
                                <svg class="h-10 w-10 text-sky-400" viewBox="0 0 52 52" fill="none" aria-hidden="true">
                                    <circle cx="26" cy="26" r="24" stroke="currentColor" stroke-width="1.5" pathLength="1" class="[stroke-dasharray:1] [stroke-dashoffset:1] [animation:draw-stroke_.9s_.15s_cubic-bezier(.65,0,.35,1)_forwards]" />
                                    <path d="M15 27l7 7 15-16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" pathLength="1" class="[stroke-dasharray:1] [stroke-dashoffset:1] [animation:draw-stroke_.5s_.9s_cubic-bezier(.65,0,.35,1)_forwards]" />
                                </svg>
                            </div>
                            <h2 class="t-h2 mt-6 text-white">{{ t('reserve.done_title') }}</h2>
                            <p class="t-lead mt-3 max-w-lg text-slate-300">{{ t('reserve.done_body', { ref: done }) }}</p>
                            
                            <div class="mt-8 inline-block rounded-2xl border border-sky-400/30 bg-white/5 px-7 py-4 font-mono text-3xl tracking-[0.2em] text-sky-300 shadow-inner" dir="ltr">
                                {{ done }}
                            </div>
                            
                            <div class="mt-10 flex flex-wrap gap-4">
                                <a v-if="site.whatsappLink(summaryText)" :href="site.whatsappLink(summaryText)" target="_blank" rel="noopener" class="btn btn-sky">
                                    <Icon name="whatsapp" :size="18" /> {{ t('cta.whatsapp') }}
                                </a>
                                <button type="button" class="btn btn-ghost-light" @click="again">{{ t('reserve.another') }}</button>
                            </div>
                        </div>
                    </section>

                    <!-- Paused Screen -->
                    <section v-else-if="!enabled" key="paused" class="max-w-xl">
                        <p class="t-lead text-charcoal/80">{{ t('reserve.paused') }}</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a v-if="site.telLink" :href="site.telLink" class="btn btn-sky"><Icon name="phone" :size="18" /> {{ t('cta.call') }}</a>
                            <a v-if="site.whatsappLink()" :href="site.whatsappLink()" target="_blank" rel="noopener" class="btn btn-ghost-dark"><Icon name="whatsapp" :size="18" /> {{ t('cta.whatsapp') }}</a>
                        </div>
                    </section>

                    <!-- Main Booking Form -->
                    <form
                        v-else key="form"
                        class="-mt-24 sm:-mt-28 rounded-3xl bg-white/95 p-6 sm:p-10 shadow-2xl shadow-sky-950/10 ring-1 ring-sky-100 backdrop-blur-md"
                        novalidate @submit.prevent="submit"
                    >
                        <div class="grid gap-8">
                            <!-- Step 1: Date & Selected Hours -->
                            <div>
                                <div class="flex items-center gap-3 mb-6">
                                    <span class="grid h-8 w-8 place-items-center rounded-full bg-sky-500 text-xs font-bold text-white shadow-md shadow-sky-500/30">1</span>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">{{ t('reserve.step_when', 'Select Date & Hour') }}</h3>
                                        <p class="text-xs text-slate-500">{{ t('reserve.when_hint', 'Choose your preferred day and exact seating time slot') }}</p>
                                    </div>
                                </div>

                                <!-- Match Night Picker (if any matches active) -->
                                <div v-if="events.length" class="mb-6 rounded-2xl border border-sky-100 bg-sky-50/60 p-4">
                                    <label class="label text-sky-900" for="r-match">
                                        <span class="inline-flex items-center gap-1.5 font-bold">
                                            <Icon name="screen" :size="16" class="text-sky-600" />
                                            {{ t('reserve.match', 'Attending a Match Night?') }}
                                        </span>
                                    </label>
                                    <select id="r-match" v-model="form.event_id" class="field mt-1">
                                        <option value="">{{ t('reserve.match_none', 'No match / Standard table reservation') }}</option>
                                        <option v-for="e in events" :key="e.id" :value="String(e.id)">{{ eventLabel(e) }}</option>
                                    </select>
                                    <p v-if="errors.event_id" class="mt-1 text-sm text-rose-500">{{ errors.event_id }}</p>
                                </div>

                                <!-- Date Picker -->
                                <div class="mb-6">
                                    <label class="label" for="r-date">{{ t('reserve.date', 'Reservation Date') }}</label>
                                    <div class="relative">
                                        <input
                                            id="r-date"
                                            v-model="form.date"
                                            class="field font-medium text-slate-800"
                                            type="date"
                                            :min="today"
                                            :max="maxDate"
                                            required
                                        >
                                    </div>
                                    <p v-if="closedDay" class="mt-1 text-sm text-rose-500">{{ t('reserve.closed_day') }}</p>
                                </div>

                                <!-- Time of Day Filters -->
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="label !mb-0">{{ t('reserve.time_slots', 'Select Available Hour Slot') }}</label>
                                        <span v-if="selectedSlotObject" class="text-xs font-semibold text-sky-600 bg-sky-50 border border-sky-200 px-2.5 py-0.5 rounded-full">
                                            {{ formatClock(selectedSlotObject.time, locale) }}
                                        </span>
                                    </div>

                                    <!-- Filter tabs -->
                                    <div class="flex flex-wrap gap-1.5 p-1 bg-slate-100/80 rounded-xl mb-4 border border-slate-200/60">
                                        <button
                                            type="button"
                                            class="flex-1 min-w-[4.5rem] py-1.5 px-2 rounded-lg text-xs font-semibold transition-all duration-200"
                                            :class="slotFilter === 'all' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                            @click="slotFilter = 'all'"
                                        >
                                            {{ locale === 'ar' ? 'جميع الساعات' : 'All Hours' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="flex-1 min-w-[4.5rem] py-1.5 px-2 rounded-lg text-xs font-semibold transition-all duration-200"
                                            :class="slotFilter === 'morning' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                            @click="slotFilter = 'morning'"
                                        >
                                            {{ locale === 'ar' ? 'الصباح' : 'Morning' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="flex-1 min-w-[4.5rem] py-1.5 px-2 rounded-lg text-xs font-semibold transition-all duration-200"
                                            :class="slotFilter === 'afternoon' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                            @click="slotFilter = 'afternoon'"
                                        >
                                            {{ locale === 'ar' ? 'الظهيرة' : 'Afternoon' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="flex-1 min-w-[4.5rem] py-1.5 px-2 rounded-lg text-xs font-semibold transition-all duration-200"
                                            :class="slotFilter === 'evening' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                            @click="slotFilter = 'evening'"
                                        >
                                            {{ locale === 'ar' ? 'المساء' : 'Evening' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="flex-1 min-w-[4.5rem] py-1.5 px-2 rounded-lg text-xs font-semibold transition-all duration-200"
                                            :class="slotFilter === 'night' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                            @click="slotFilter = 'night'"
                                        >
                                            {{ locale === 'ar' ? 'السهرة' : 'Late Night' }}
                                        </button>
                                    </div>

                                    <!-- Interactive Selected Hours Chips -->
                                    <div v-if="filteredSlots.length" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2.5 max-h-56 overflow-y-auto p-1 [scrollbar-width:thin]">
                                        <button
                                            v-for="s in filteredSlots"
                                            :key="s.value"
                                            type="button"
                                            class="group relative flex flex-col items-center justify-center rounded-xl p-2.5 text-center border transition-all duration-200"
                                            :class="form.slot === s.value
                                                ? 'bg-sky-500 text-white border-sky-500 shadow-md shadow-sky-500/30 scale-[1.02]'
                                                : 'bg-white hover:bg-sky-50/70 border-slate-200 text-slate-800 hover:border-sky-300'"
                                            @click="form.slot = s.value"
                                        >
                                            <span class="font-bold text-sm leading-tight">
                                                {{ formatClock(s.time, locale) }}
                                            </span>
                                            <span
                                                v-if="s.isPrime"
                                                class="mt-1 text-[0.65rem] font-medium tracking-tight rounded px-1"
                                                :class="form.slot === s.value ? 'bg-white/20 text-white' : 'text-amber-600 bg-amber-50'"
                                            >
                                                ✦ {{ locale === 'ar' ? 'وقت ذروة' : 'Prime' }}
                                            </span>
                                            <span
                                                v-else-if="s.late"
                                                class="mt-1 text-[0.65rem] opacity-75"
                                            >
                                                {{ formatDate(`${s.date}T12:00:00Z`, locale, { weekday: 'short' }) }}
                                            </span>
                                        </button>
                                    </div>
                                    <p v-else class="text-xs text-slate-500 p-4 text-center rounded-xl bg-slate-50">
                                        {{ locale === 'ar' ? 'لا توجد ساعات متاحة في هذه الفترة، يرجى اختيار فترة أخرى.' : 'No slots available in this time period, please choose another.' }}
                                    </p>
                                    <p v-if="errors.slot" class="mt-1.5 text-sm text-rose-500">{{ errors.slot }}</p>
                                </div>

                                <!-- Guests Stepper -->
                                <div class="mt-6 pt-6 border-t border-slate-100">
                                    <label class="label">{{ t('reserve.guests', 'Number of Guests') }}</label>
                                    <div class="flex flex-wrap items-center gap-4">
                                        <div class="inline-flex items-center rounded-2xl border border-sky-200 bg-white p-1.5 shadow-sm">
                                            <button
                                                type="button"
                                                class="grid h-10 w-10 place-items-center rounded-xl text-lg font-bold transition-all hover:bg-sky-500 hover:text-white disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-current"
                                                :disabled="form.guests <= minGuests"
                                                :aria-label="t('reserve.fewer')"
                                                @click="bumpGuests(-1)"
                                            >−</button>
                                            <output class="tabular w-16 text-center font-display text-2xl font-bold text-slate-900" aria-live="polite">{{ form.guests }}</output>
                                            <button
                                                type="button"
                                                class="grid h-10 w-10 place-items-center rounded-xl text-lg font-bold transition-all hover:bg-sky-500 hover:text-white disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-current"
                                                :disabled="form.guests >= maxGuests"
                                                :aria-label="t('reserve.more')"
                                                @click="bumpGuests(1)"
                                            >+</button>
                                        </div>

                                        <!-- Quick Presets -->
                                        <div class="flex flex-wrap gap-1.5">
                                            <button
                                                v-for="g in [2, 4, 6, 8]"
                                                :key="g"
                                                type="button"
                                                class="rounded-xl px-3 py-1.5 text-xs font-semibold border transition-all"
                                                :class="form.guests === g ? 'bg-sky-100 border-sky-400 text-sky-800' : 'bg-white border-slate-200 text-slate-600 hover:border-sky-200'"
                                                @click="form.guests = g"
                                            >
                                                {{ g }} {{ locale === 'ar' ? 'ضيوف' : 'Guests' }}
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="errors.guests" class="mt-1 text-sm text-rose-500">{{ errors.guests }}</p>
                                </div>
                            </div>

                            <!-- Seating Area Options -->
                            <div class="pt-6 border-t border-slate-100">
                                <label class="label mb-3">{{ t('reserve.seating', 'Preferred Seating Zone') }}</label>
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    <label
                                        v-for="o in seatingOptions"
                                        :key="o.value"
                                        class="group relative flex cursor-pointer flex-col items-start gap-2.5 rounded-2xl border p-4 transition-all duration-200 focus-within:ring-2 focus-within:ring-sky-400"
                                        :class="form.seating === o.value
                                            ? 'border-sky-500 bg-gradient-to-b from-sky-50 to-white shadow-md shadow-sky-500/10'
                                            : 'border-slate-200 bg-white hover:border-sky-300 hover:-translate-y-0.5'"
                                    >
                                        <input v-model="form.seating" type="radio" name="seating" :value="o.value" class="sr-only">
                                        <span class="grid h-9 w-9 place-items-center rounded-xl" :class="form.seating === o.value ? 'bg-sky-500 text-white' : 'bg-sky-50 text-sky-600'">
                                            <Icon :name="o.icon" :size="18" />
                                        </span>
                                        <div>
                                            <span class="block text-sm font-bold text-slate-900">{{ t(o.label) }}</span>
                                            <span class="block text-[0.7rem] text-slate-500 mt-0.5">{{ locale === 'ar' ? o.desc_ar : o.desc_en }}</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Step 2: Guest Details -->
                            <div class="pt-6 border-t border-slate-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <span class="grid h-8 w-8 place-items-center rounded-full bg-sky-500 text-xs font-bold text-white shadow-md shadow-sky-500/30">2</span>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">{{ t('reserve.step_who', 'Guest Contact Details') }}</h3>
                                        <p class="text-xs text-slate-500">{{ t('reserve.who_hint', 'We will send instant WhatsApp & SMS confirmation') }}</p>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label class="label" for="r-name">{{ t('reserve.name') }}</label>
                                        <input id="r-name" v-model="form.name" class="field" autocomplete="name" placeholder="e.g. Sultan Al-Zaabi" required maxlength="80" :aria-invalid="!!errors.name">
                                        <p v-if="errors.name" class="mt-1 text-sm text-rose-500">{{ errors.name }}</p>
                                    </div>
                                    <div>
                                        <label class="label" for="r-phone">{{ t('reserve.phone') }}</label>
                                        <input id="r-phone" v-model="form.phone" class="field" type="tel" dir="ltr" inputmode="tel" autocomplete="tel" placeholder="050 XXX XXXX" required :aria-invalid="!!errors.phone">
                                        <p v-if="errors.phone" class="mt-1 text-sm text-rose-500">{{ errors.phone }}</p>
                                    </div>
                                    <div>
                                        <label class="label" for="r-email">{{ t('reserve.email') }} <span class="text-xs opacity-50 font-normal">({{ locale === 'ar' ? 'اختياري' : 'Optional' }})</span></label>
                                        <input id="r-email" v-model="form.email" class="field" type="email" dir="ltr" autocomplete="email" placeholder="name@domain.com" :aria-invalid="!!errors.email">
                                        <p v-if="errors.email" class="mt-1 text-sm text-rose-500">{{ errors.email }}</p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="label" for="r-notes">{{ t('reserve.notes') }}</label>
                                        <textarea id="r-notes" v-model="form.notes" class="field min-h-24" maxlength="500" :placeholder="t('reserve.notes_hint', 'Special requests, birthday celebration, shisha preference...')" />
                                    </div>
                                </div>
                            </div>

                            <!-- Honeypot -->
                            <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                                <label for="r-website">Website</label>
                                <input id="r-website" v-model="form.website" tabindex="-1" autocomplete="off">
                            </div>

                            <!-- Submit Button & Confirmation Note -->
                            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <p v-if="failed" class="w-full text-sm text-rose-500" role="alert">{{ t('errors.generic') }}</p>
                                
                                <button
                                    type="submit"
                                    class="btn btn-sky group w-full sm:w-auto px-8"
                                    :disabled="sending || !allSlots.length"
                                >
                                    <svg v-if="sending" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="2.5" /><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" /></svg>
                                    <span v-if="!sending" class="flex items-center gap-2">
                                        {{ t('reserve.submit', 'Confirm Booking Request') }}
                                        <Icon name="chevron" :size="18" class="transition-transform duration-300 group-hover:translate-x-1 rtl:rotate-180 rtl:group-hover:-translate-x-1" />
                                    </span>
                                </button>

                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <Icon name="whatsapp" :size="16" class="text-emerald-500 shrink-0" />
                                    <span>{{ t('reserve.confirm_note', 'Instant reservation ticket generated') }}</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </transition>

                <!-- Sidebar Summary & Hours Widget -->
                <aside class="space-y-6 lg:sticky lg:top-[calc(var(--header-h)+2rem)] lg:self-start">
                    <!-- Live Booking Summary Card -->
                    <div class="rounded-3xl border border-sky-100 bg-white p-6 shadow-xl shadow-sky-900/5">
                        <h4 class="text-xs font-bold uppercase tracking-widest text-sky-600 mb-4">{{ locale === 'ar' ? 'ملخص الحجز' : 'Booking Summary' }}</h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                                <span class="text-slate-500">{{ t('reserve.date') }}</span>
                                <span class="font-bold text-slate-800">{{ formatDate(`${form.date}T12:00:00Z`, locale, { weekday: 'short', month: 'short', day: 'numeric' }) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                                <span class="text-slate-500">{{ t('reserve.time') }}</span>
                                <span class="font-bold text-sky-700 bg-sky-50 px-2.5 py-0.5 rounded-full">{{ selectedSlotObject ? formatClock(selectedSlotObject.time, locale) : '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                                <span class="text-slate-500">{{ t('reserve.guests') }}</span>
                                <span class="font-bold text-slate-800">{{ form.guests }} {{ locale === 'ar' ? 'ضيوف' : 'Guests' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-slate-500">{{ t('reserve.seating') }}</span>
                                <span class="font-bold text-slate-800">{{ seatingOptions.find(o => o.value === form.seating)?.desc_en ? t(seatingOptions.find(o => o.value === form.seating).label) : t('reserve.seating_any') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Arch Image Showcase -->
                    <figure v-reveal="'curtain'" class="relative hidden aspect-[4/5] overflow-hidden rounded-t-full lg:block shadow-lg">
                        <img v-parallax="0.06" :src="SIDE_PHOTO" alt="Loyal VIP Lounge" loading="lazy" class="h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent" />
                        <figcaption class="absolute inset-x-0 bottom-0 p-6 font-display text-lg font-semibold leading-snug text-white">
                            {{ t('reserve.aside_quote', 'Specialty coffee, luxury shisha, and warm Gulf hospitality.') }}
                        </figcaption>
                    </figure>

                    <!-- Operating Hours Widget -->
                    <div v-reveal="{ delay: 120 }" class="rounded-3xl border border-sky-100 bg-white p-6 shadow-sm">
                        <h2 class="mb-3 flex items-center gap-2 font-display text-lg font-semibold text-slate-900">
                            <Icon name="clock" :size="18" class="text-sky-500" />
                            {{ t('home.hours_title') }}
                        </h2>
                        <HoursTable />
                    </div>

                    <!-- Contact shortcuts -->
                    <div v-reveal="{ delay: 240 }" class="flex flex-col gap-2.5">
                        <a v-if="site.telLink" :href="site.telLink" class="group flex items-center gap-3 p-3 rounded-2xl border border-sky-100 bg-white text-sm font-semibold text-slate-700 hover:border-sky-300 hover:text-sky-600 transition-all">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-sky-50 text-sky-600"><Icon name="phone" :size="16" /></span>
                            <span dir="ltr">{{ site.settings.phone }}</span>
                        </a>
                        <a v-if="site.whatsappLink()" :href="site.whatsappLink()" target="_blank" rel="noopener" class="group flex items-center gap-3 p-3 rounded-2xl border border-emerald-100 bg-white text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-600 transition-all">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><Icon name="whatsapp" :size="16" /></span>
                            {{ t('cta.whatsapp') }}
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</template>

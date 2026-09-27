<script setup>
import { computed, ref } from 'vue';
import TransInput from '../components/TransInput.vue';
import ImageField from '../components/ImageField.vue';
import Toggle from '../components/Toggle.vue';
import { api, errorMessage, fieldErrors } from '../api';
import { toast } from '../toast';

const tabs = [
    ['general', 'General'], ['contact', 'Contact & map'], ['hours', 'Hours & amenities'],
    ['reservation', 'Reservations'], ['brand', 'Brand colours'], ['seo', 'SEO & analytics'],
];
const tab = ref('general');
const s = ref(null);
const media = ref({});
const errors = ref({});
const saving = ref(false);

const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const PAGES = [['home', 'Home'], ['menu', 'Menu'], ['gallery', 'Gallery'], ['match-nights', 'Match nights'], ['reserve', 'Reserve'], ['contact', 'Contact']];
const THEME = [
    ['primary', 'Emerald (primary)', '#1B4332'], ['accent', 'Gold (accent & buttons)', '#C9A227'],
    ['background', 'Ivory (background)', '#F5F0E6'], ['text', 'Charcoal (text)', '#21201F'], ['warm', 'Terracotta (detail)', '#A85C32'],
];
const AMENITY_ICONS = ['parking', 'screen', 'shisha', 'wifi', 'family', 'moon', 'sun', 'coffee', 'leaf', 'football', 'juice', 'plate'];

/** Make sure every nested object the form binds to exists. */
function normalise(d) {
    const o = (v) => (v && typeof v === 'object' && !Array.isArray(v) ? v : {});
    d.site_name = o(d.site_name); d.tagline = o(d.tagline);
    d.announcement = { enabled: false, link: '', ...o(d.announcement), text: o(d.announcement?.text) };
    d.whatsapp_message = o(d.whatsapp_message);
    d.address = { street: o(d.address?.street), area: o(d.address?.area) };
    d.maps = { lat: null, lng: null, embed_url: '', directions_url: '', ...o(d.maps) };
    d.socials = { instagram: '', tiktok: '', facebook: '', google: '', ...o(d.socials) };
    const byDay = Object.fromEntries((d.hours || []).map((h) => [h.day, h]));
    d.hours = DAYS.map((_, i) => ({ day: i, open: '08:00', close: '03:00', closed: false, ...byDay[i] }));
    d.hours_note = o(d.hours_note);
    d.amenities = (d.amenities || []).map((a) => ({ icon: a.icon, label: o(a.label) }));
    d.rating = { value: 4, count: 0, url: '', ...o(d.rating) };
    d.reservation = { enabled: true, min_guests: 1, max_guests: 30, slot_minutes: 30, lead_minutes: 30, notify_email: null, ...o(d.reservation) };
    d.theme = { ...Object.fromEntries(THEME.map(([k, , def]) => [k, def])), ...o(d.theme) };
    d.seo = o(d.seo);
    PAGES.forEach(([p]) => (d.seo[p] = { title: o(d.seo[p]?.title), description: o(d.seo[p]?.description) }));
    d.analytics = { ga4_id: '', meta_pixel_id: '', ...o(d.analytics) };
    return d;
}

async function load() {
    const { data } = await api.get('/settings');
    media.value = data._media || {};
    delete data._media;
    s.value = normalise(data);
}
load().catch((e) => toast(errorMessage(e), 'error'));

async function save() {
    saving.value = true;
    errors.value = {};
    try {
        const { data } = await api.put('/settings', { settings: s.value });
        media.value = data._media || {};
        delete data._media;
        s.value = normalise(data);
        toast('Settings saved. The website updates immediately.');
    } catch (e) {
        const f = fieldErrors(e);
        errors.value = f ? Object.fromEntries(Object.entries(f).map(([k, v]) => [k.replace(/^settings\./, ''), v])) : {};
        toast(f ? Object.values(f)[0] : errorMessage(e), 'error');
    } finally {
        saving.value = false;
    }
}

function sameForAll() {
    const first = s.value.hours[0];
    s.value.hours = s.value.hours.map((h) => ({ ...h, open: first.open, close: first.close, closed: first.closed }));
}
const pastMidnight = (h) => !h.closed && h.close <= h.open;
const waPreview = computed(() => (s.value?.whatsapp ? `https://wa.me/${s.value.whatsapp}` : ''));
</script>

<template>
    <div class="max-w-5xl">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <h1 class="font-display text-3xl">Settings</h1>
            <button type="button" class="a-btn a-btn-primary" :disabled="saving || !s" @click="save">{{ saving ? 'Saving…' : 'Save settings' }}</button>
        </div>

        <div class="mb-6 flex flex-wrap gap-1 border-b border-stone-200">
            <button v-for="[k, l] in tabs" :key="k" type="button" class="-mb-px border-b-2 px-4 py-2 text-sm"
                :class="tab === k ? 'border-emerald font-medium text-emerald' : 'border-transparent text-stone-500 hover:text-charcoal'" @click="tab = k">{{ l }}</button>
        </div>

        <div v-if="!s" class="text-sm text-stone-500">Loading…</div>

        <div v-else class="a-card space-y-8 p-6">
            <!-- General -->
            <template v-if="tab === 'general'">
                <div><label class="a-label">Business name</label><TransInput v-model="s.site_name" /></div>
                <div><label class="a-label">Tagline</label><TransInput v-model="s.tagline" /></div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div><label class="a-label">Logo (on light backgrounds)</label><ImageField v-model="s.logo" :preview-url="media.logo" folder="brand" /></div>
                    <div><label class="a-label">Logo (on dark backgrounds)</label><ImageField v-model="s.logo_light" :preview-url="media.logo_light" folder="brand" /></div>
                </div>
                <p class="a-hint -mt-4">Until a logo is uploaded the site shows an elegant text wordmark. SVG or transparent PNG works best.</p>
                <fieldset class="rounded-lg border border-stone-200 p-4">
                    <legend class="px-2 text-sm font-semibold">Announcement bar</legend>
                    <Toggle v-model="s.announcement.enabled" label="Show a strip above the header on every page" />
                    <div class="mt-4"><label class="a-label">Text</label><TransInput v-model="s.announcement.text" /></div>
                    <div class="mt-4"><label class="a-label">Link (optional)</label><input v-model="s.announcement.link" class="a-input" dir="ltr" placeholder="/match-nights"></div>
                </fieldset>
            </template>

            <!-- Contact -->
            <template v-if="tab === 'contact'">
                <div class="grid gap-6 md:grid-cols-3">
                    <div><label class="a-label">Phone (as displayed)</label><input v-model="s.phone" class="a-input" dir="ltr" placeholder="050 278 3288"></div>
                    <div><label class="a-label">Phone for tap-to-call</label><input v-model="s.phone_intl" class="a-input" dir="ltr" placeholder="+971502783288"></div>
                    <div>
                        <label class="a-label">WhatsApp number</label><input v-model="s.whatsapp" class="a-input" dir="ltr" placeholder="971502783288">
                        <p class="a-hint">Digits only with country code. <a v-if="waPreview" :href="waPreview" target="_blank" rel="noopener" class="text-emerald underline">Test</a></p>
                        <p v-if="errors.whatsapp" class="a-error">{{ errors.whatsapp }}</p>
                    </div>
                </div>
                <div><label class="a-label">WhatsApp pre-filled message</label><TransInput v-model="s.whatsapp_message" /></div>
                <div><label class="a-label">Email (optional, shown on the contact page)</label><input v-model="s.email" type="email" class="a-input max-w-md" dir="ltr"></div>
                <div><label class="a-label">Street</label><TransInput v-model="s.address.street" /></div>
                <div><label class="a-label">Area / city</label><TransInput v-model="s.address.area" /></div>
                <fieldset class="rounded-lg border border-stone-200 p-4">
                    <legend class="px-2 text-sm font-semibold">Google Maps</legend>
                    <label class="a-label">Embed URL</label>
                    <input v-model="s.maps.embed_url" class="a-input" dir="ltr">
                    <p class="a-hint">Google Maps → your listing → Share → Embed a map → copy only the <code>src="…"</code> address.</p>
                    <label class="a-label mt-4">Directions link</label>
                    <input v-model="s.maps.directions_url" class="a-input" dir="ltr">
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div><label class="a-label">Latitude</label><input v-model.number="s.maps.lat" type="number" step="any" class="a-input" dir="ltr"></div>
                        <div><label class="a-label">Longitude</label><input v-model.number="s.maps.lng" type="number" step="any" class="a-input" dir="ltr"></div>
                    </div>
                    <p class="a-hint">Used in Google's structured data so the listing and website point to the same pin.</p>
                </fieldset>
                <fieldset class="grid gap-4 rounded-lg border border-stone-200 p-4 md:grid-cols-2">
                    <legend class="px-2 text-sm font-semibold">Social & review links</legend>
                    <div v-for="k in ['instagram', 'tiktok', 'facebook', 'google']" :key="k">
                        <label class="a-label capitalize">{{ k === 'google' ? 'Google review link' : k }}</label>
                        <input v-model="s.socials[k]" class="a-input" dir="ltr" placeholder="https://">
                    </div>
                </fieldset>
                <fieldset class="grid gap-4 rounded-lg border border-stone-200 p-4 md:grid-cols-3">
                    <legend class="px-2 text-sm font-semibold">Google rating (shown on the site)</legend>
                    <div><label class="a-label">Stars</label><input v-model.number="s.rating.value" type="number" step="0.1" min="0" max="5" class="a-input"></div>
                    <div><label class="a-label">Number of reviews</label><input v-model.number="s.rating.count" type="number" min="0" class="a-input"></div>
                    <div><label class="a-label">Link to reviews</label><input v-model="s.rating.url" class="a-input" dir="ltr"></div>
                </fieldset>
            </template>

            <!-- Hours -->
            <template v-if="tab === 'hours'">
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="font-semibold">Opening hours</h2>
                        <button type="button" class="a-btn a-btn-ghost a-btn-sm" @click="sameForAll">Copy Sunday to every day</button>
                    </div>
                    <div class="divide-y divide-stone-100">
                        <div v-for="h in s.hours" :key="h.day" class="flex flex-wrap items-center gap-4 py-2.5">
                            <span class="w-28 text-sm font-medium">{{ DAYS[h.day] }}</span>
                            <input v-model="h.open" type="time" class="a-input w-32" :disabled="h.closed">
                            <span class="text-stone-400">to</span>
                            <input v-model="h.close" type="time" class="a-input w-32" :disabled="h.closed">
                            <Toggle v-model="h.closed" label="Closed" />
                            <span v-if="pastMidnight(h)" class="text-xs text-stone-500">closes after midnight</span>
                        </div>
                    </div>
                </div>
                <div><label class="a-label">Note under the hours</label><TransInput v-model="s.hours_note" /></div>
                <div>
                    <h2 class="mb-3 font-semibold">Amenities</h2>
                    <div v-for="(a, i) in s.amenities" :key="i" class="mb-3 flex flex-wrap items-start gap-3">
                        <select v-model="a.icon" class="a-input w-36"><option v-for="ic in AMENITY_ICONS" :key="ic" :value="ic">{{ ic }}</option></select>
                        <div class="min-w-0 flex-1"><TransInput v-model="a.label" /></div>
                        <button type="button" class="a-btn a-btn-danger a-btn-sm mt-5" @click="s.amenities.splice(i, 1)">Remove</button>
                    </div>
                    <button type="button" class="a-btn a-btn-ghost a-btn-sm" @click="s.amenities.push({ icon: 'star', label: {} })">Add amenity</button>
                </div>
            </template>

            <!-- Reservations -->
            <template v-if="tab === 'reservation'">
                <Toggle v-model="s.reservation.enabled" label="Accept online reservations" />
                <p class="a-hint -mt-6">When off, the reserve page asks guests to call or WhatsApp instead.</p>
                <div class="grid gap-6 md:grid-cols-4">
                    <div><label class="a-label">Min guests</label><input v-model.number="s.reservation.min_guests" type="number" min="1" class="a-input"></div>
                    <div><label class="a-label">Max guests per booking</label><input v-model.number="s.reservation.max_guests" type="number" min="1" class="a-input"></div>
                    <div><label class="a-label">Time slots every (min)</label>
                        <select v-model.number="s.reservation.slot_minutes" class="a-input"><option v-for="n in [15, 30, 60]" :key="n" :value="n">{{ n }}</option></select></div>
                    <div><label class="a-label">Minimum notice (min)</label><input v-model.number="s.reservation.lead_minutes" type="number" min="0" class="a-input"></div>
                </div>
                <div>
                    <label class="a-label">Email me new bookings & messages (optional)</label>
                    <input v-model="s.reservation.notify_email" type="email" class="a-input max-w-md" dir="ltr">
                    <p class="a-hint">Requires mail settings in the server's .env file. Never shown on the website.</p>
                    <p v-if="errors['reservation.notify_email']" class="a-error">{{ errors['reservation.notify_email'] }}</p>
                </div>
            </template>

            <!-- Brand -->
            <template v-if="tab === 'brand'">
                <p class="text-sm text-stone-500">From the Loyal Brand Design System. Changes apply across the whole website instantly. Keep enough contrast between text and background.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div v-for="[k, l, def] in THEME" :key="k" class="flex items-center gap-3">
                        <input v-model="s.theme[k]" type="color" class="h-11 w-14 cursor-pointer rounded border border-stone-300">
                        <div class="flex-1">
                            <label class="a-label">{{ l }}</label>
                            <input v-model="s.theme[k]" class="a-input font-mono" dir="ltr">
                        </div>
                        <button type="button" class="a-btn a-btn-ghost a-btn-sm mt-5" :disabled="s.theme[k]?.toUpperCase() === def" @click="s.theme[k] = def">Reset</button>
                    </div>
                </div>
                <div class="overflow-hidden rounded-xl" :style="{ background: s.theme.background, color: s.theme.text }">
                    <div class="p-6" :style="{ background: s.theme.primary, color: s.theme.background }">
                        <p class="font-display text-2xl">Stay a while.</p>
                        <span class="mt-4 inline-block rounded-full px-5 py-2 text-sm" :style="{ background: s.theme.accent, color: s.theme.text }">Reserve a table</span>
                    </div>
                    <p class="p-6 text-sm">Body text on the page background, with a <span :style="{ color: s.theme.warm }">terracotta detail</span>.</p>
                </div>
            </template>

            <!-- SEO -->
            <template v-if="tab === 'seo'">
                <p class="text-sm text-stone-500">Leave empty to use the built-in, keyword-researched defaults (shisha cafe Abu Dhabi, Madinat Zayed, match nights…).</p>
                <details v-for="[p, l] in PAGES" :key="p" class="rounded-lg border border-stone-200 p-4">
                    <summary class="cursor-pointer text-sm font-semibold">{{ l }} page</summary>
                    <div class="mt-4"><label class="a-label">Title (about 50–60 characters)</label><TransInput v-model="s.seo[p].title" /></div>
                    <div class="mt-4"><label class="a-label">Description (about 150 characters)</label><TransInput v-model="s.seo[p].description" multiline :rows="2" /></div>
                </details>
                <div><label class="a-label">Social sharing image (1200 × 630)</label><ImageField v-model="s.og_image" :preview-url="media.og_image" folder="brand" /></div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div><label class="a-label">Google Analytics 4 ID</label><input v-model="s.analytics.ga4_id" class="a-input" dir="ltr" placeholder="G-XXXXXXX"></div>
                    <div><label class="a-label">Meta Pixel ID</label><input v-model="s.analytics.meta_pixel_id" class="a-input" dir="ltr"></div>
                </div>
            </template>
        </div>
    </div>
</template>

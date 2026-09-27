import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

export const CAFE_TZ = 'Asia/Dubai';

/** Current weekday (0=Sun) and minutes-since-midnight in the cafe's timezone, whatever the visitor's clock. */
export function cafeNow(date = new Date()) {
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: CAFE_TZ, weekday: 'short', hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
    }).formatToParts(date);
    const get = (t) => parts.find((p) => p.type === t)?.value;
    const day = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(get('weekday'));
    return { day, minutes: +get('hour') * 60 + +get('minute') };
}

const toMin = (hhmm) => {
    const [h, m] = String(hhmm || '0:0').split(':').map(Number);
    return h * 60 + m;
};

/** "03:00" → "3 AM" / "3 ص" (Western digits, as commonly used in the UAE). */
export function formatClock(hhmm, locale) {
    const [h, m] = String(hhmm).split(':').map(Number);
    const d = new Date(Date.UTC(2000, 0, 1, h, m));
    return new Intl.DateTimeFormat(locale === 'ar' ? 'ar-AE-u-nu-latn' : 'en-AE', {
        hour: 'numeric', minute: m ? '2-digit' : undefined, timeZone: 'UTC',
    }).format(d);
}

export function formatDate(iso, locale, opts) {
    return new Intl.DateTimeFormat(locale === 'ar' ? 'ar-AE-u-nu-latn' : 'en-GB', { timeZone: CAFE_TZ, ...opts }).format(new Date(iso));
}

/**
 * Live open/closed status from the weekly hours setting. Handles shifts past midnight
 * (08:00 → 03:00): at 01:30 on Tuesday we are inside Monday's shift.
 */
export function useOpenStatus(hoursRef) {
    const tick = ref(Date.now());
    let timer;
    onMounted(() => (timer = setInterval(() => (tick.value = Date.now()), 60_000)));
    onBeforeUnmount(() => clearInterval(timer));

    return computed(() => {
        tick.value; // re-evaluate every minute
        const hours = (hoursRef.value || []).reduce((acc, h) => ((acc[h.day] = h), acc), {});
        if (!Object.keys(hours).length) return null;
        const { day, minutes } = cafeNow();

        const shiftFor = (d) => {
            const h = hours[(d + 7) % 7];
            if (!h || h.closed) return null;
            const open = toMin(h.open);
            let close = toMin(h.close);
            if (close <= open) close += 1440;
            return { open, close, h };
        };

        // Inside yesterday's late shift, or today's shift?
        const y = shiftFor(day - 1);
        if (y && minutes + 1440 < y.close) return { open: true, closesAt: y.h.close, minutesLeft: y.close - (minutes + 1440) };
        const t = shiftFor(day);
        if (t && minutes >= t.open && minutes < t.close) return { open: true, closesAt: t.h.close, minutesLeft: t.close - minutes };
        if (t && minutes < t.open) return { open: false, opensAt: t.h.open, opensDay: null };
        for (let i = 1; i <= 7; i++) {
            const n = shiftFor(day + i);
            if (n) return { open: false, opensAt: n.h.open, opensDay: i === 1 ? null : (day + i) % 7 };
        }
        return { open: false };
    });
}

/** Day or night mood for the hero, by the cafe's local clock (night 17:00–05:00). */
export function useCafeMood() {
    const { minutes } = cafeNow();
    return minutes >= 17 * 60 || minutes < 5 * 60 ? 'night' : 'day';
}

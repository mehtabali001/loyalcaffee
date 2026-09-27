/** Display helpers for the admin (everything shown in the cafe's own timezone). */
export const TZ = 'Asia/Dubai';

export const tr = (v, l = 'en') => (v && typeof v === 'object' ? v[l] || Object.values(v).find(Boolean) || '' : v ?? '');

export function fmtDateTime(iso) {
    if (!iso) return '';
    return new Intl.DateTimeFormat('en-GB', { timeZone: TZ, weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }).format(new Date(iso));
}
export function fmtDate(ymd) {
    if (!ymd) return '';
    return new Intl.DateTimeFormat('en-GB', { timeZone: 'UTC', weekday: 'short', day: 'numeric', month: 'short' }).format(new Date(`${String(ymd).slice(0, 10)}T12:00:00Z`));
}

/** ISO instant → "YYYY-MM-DDTHH:mm" in Abu Dhabi time for <input type="datetime-local">. */
export function isoToLocalInput(iso) {
    if (!iso) return '';
    const p = Object.fromEntries(new Intl.DateTimeFormat('en-GB', {
        timeZone: TZ, year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
    }).formatToParts(new Date(iso)).map((x) => [x.type, x.value]));
    return `${p.year}-${p.month}-${p.day}T${p.hour}:${p.minute}`;
}
/** Back to the server as a wall-clock string; Laravel parses it in the app timezone (Asia/Dubai). */
export const localInputToServer = (v) => (v ? v.replace('T', ' ') : null);

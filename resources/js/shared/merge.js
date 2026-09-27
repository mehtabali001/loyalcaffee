/** Deep-merge DB overrides onto bundled messages. Numeric-keyed objects ("days.0") patch arrays. */
export function deepMerge(base, over) {
    if (Array.isArray(base) && over && typeof over === 'object') {
        const out = [...base];
        for (const [k, v] of Object.entries(over)) if (/^\d+$/.test(k) && v) out[+k] = v;
        return out;
    }
    if (!base || typeof base !== 'object' || !over || typeof over !== 'object') return over ?? base;
    const out = { ...base };
    for (const [k, v] of Object.entries(over)) {
        out[k] = k in base ? deepMerge(base[k], v) : v;
    }
    return out;
}

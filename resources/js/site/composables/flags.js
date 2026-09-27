/** ISO 3166 alpha-2 → flag emoji ("ae" → 🇦🇪). England/Scotland etc. use subdivision tags. */
const SUBDIVISIONS = { eng: 'gbeng', sct: 'gbsct', wls: 'gbwls' };

export function flagEmoji(code) {
    if (!code) return '';
    const c = code.toLowerCase();
    if (SUBDIVISIONS[c]) {
        const tag = [...SUBDIVISIONS[c]].map((ch) => String.fromCodePoint(0xe0000 + ch.charCodeAt(0))).join('');
        return `\u{1F3F4}${tag}\u{E007F}`;
    }
    if (!/^[a-z]{2}$/.test(c)) return '';
    return String.fromCodePoint(...[...c.toUpperCase()].map((ch) => 0x1f1e6 + ch.charCodeAt(0) - 65));
}

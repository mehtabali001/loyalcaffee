/** Brand tokens from the Loyal Brand Design System §1.7–1.8. Colours read CSS variables so the
 *  owner can re-theme from Admin → Settings without a rebuild. */
const v = (name) => `rgb(var(${name}-rgb) / <alpha-value>)`;

export default {
    content: ['./resources/views/**/*.blade.php', './resources/js/**/*.{vue,js}'],
    theme: {
        extend: {
            colors: {
                sky: { DEFAULT: v('--c-sky'), light: v('--c-sky-light'), soft: v('--c-sky-soft'), tint: v('--c-sky-tint'), dark: v('--c-sky-dark') },
                azure: { DEFAULT: v('--c-azure'), soft: v('--c-azure-soft') },
                emerald: { DEFAULT: v('--c-emerald'), night: v('--c-night') },
                gold: { DEFAULT: v('--c-gold'), soft: v('--c-gold-soft') },
                ivory: { DEFAULT: v('--c-ivory'), deep: v('--c-ivory-deep') },
                charcoal: v('--c-charcoal'),
                terracotta: v('--c-terracotta'),
            },
            fontFamily: {
                display: ['var(--font-display)'],
                body: ['var(--font-body)'],
            },
            maxWidth: { prose: '38rem', shell: '76rem' },
            letterSpacing: { hush: '0.02em' },
            transitionTimingFunction: { lounge: 'cubic-bezier(.2,.7,.2,1)' },
        },
    },
    plugins: [],
};

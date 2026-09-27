/**
 * Scroll motion for the public site, kept deliberately small: one shared IntersectionObserver
 * for reveals and one rAF-throttled scroll loop for parallax. Visitors who ask for reduced
 * motion get the content immediately and nothing drifts.
 *
 *   v-reveal                 → rise into place
 *   v-reveal="'curtain'"     → image wipes open (up | fade | scale | start | end | curtain | rule)
 *   v-reveal="{ as: 'up', delay: 120 }"
 *   v-parallax="0.12"        → drifts at 12% of scroll speed relative to the viewport centre
 */
const reduced = () => typeof window !== 'undefined' && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

/* ---------- Reveal ---------- */
let io;
const settle = (el) => {
    el.classList.add('is-revealed');
    // Hand the element back to its own transitions (hover etc.) once the entrance has played.
    el._revealTimer = setTimeout(() => {
        el.removeAttribute('data-reveal');
        el.classList.remove('is-revealed');
        el.style.removeProperty('--reveal-delay');
    }, (el._revealDelay || 0) + 1600);
};
const observer = () => (io ||= new IntersectionObserver((entries) => {
    for (const e of entries) {
        if (!e.isIntersecting) continue;
        io.unobserve(e.target);
        settle(e.target);
    }
}, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 }));

function parse(value) {
    if (!value) return { as: 'up', delay: 0 };
    if (typeof value === 'string') return { as: value, delay: 0 };
    return { as: value.as || 'up', delay: Number(value.delay) || 0 };
}

export const reveal = {
    mounted(el, { value }) {
        if (reduced() || typeof IntersectionObserver === 'undefined') return;
        const { as, delay } = parse(value);
        el._revealDelay = delay;
        el.setAttribute('data-reveal', as);
        if (delay) el.style.setProperty('--reveal-delay', `${delay}ms`);
        observer().observe(el);
    },
    unmounted(el) {
        io?.unobserve(el);
        clearTimeout(el._revealTimer);
    },
};

/* ---------- Parallax ---------- */
const layers = new Set();
let ticking = false;
let bound = false;

function frame() {
    ticking = false;
    const vh = window.innerHeight;
    for (const el of layers) {
        const r = el.parentElement?.getBoundingClientRect() || el.getBoundingClientRect();
        if (r.bottom < -200 || r.top > vh + 200) continue;
        const offset = (r.top + r.height / 2 - vh / 2) * el._speed;
        el.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0) scale(${el._scale})`;
    }
}
const request = () => {
    if (!ticking) {
        ticking = true;
        requestAnimationFrame(frame);
    }
};

export const parallax = {
    mounted(el, { value }) {
        if (reduced()) return;
        el._speed = Number(value) || 0.1;
        // Slight overscale so the drifting layer never shows its edges.
        el._scale = 1 + Math.min(Math.abs(el._speed) * 1.2, 0.25);
        el.style.willChange = 'transform';
        layers.add(el);
        if (!bound) {
            window.addEventListener('scroll', request, { passive: true });
            window.addEventListener('resize', request, { passive: true });
            bound = true;
        }
        request();
    },
    unmounted(el) {
        layers.delete(el);
        if (!layers.size && bound) {
            window.removeEventListener('scroll', request);
            window.removeEventListener('resize', request);
            bound = false;
        }
    },
};

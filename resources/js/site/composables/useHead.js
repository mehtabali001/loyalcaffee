import { watch } from 'vue';
import { useRoute } from 'vue-router';
import { useSite } from '../stores/site';

function setMeta(selector, attr, value) {
    let el = document.head.querySelector(selector);
    if (!el) return;
    el.setAttribute(attr, value);
}

/** Keeps <title>, description, canonical and hreflang in sync on client-side navigation. */
export function useHead() {
    const route = useRoute();
    const site = useSite();
    watch(
        () => route.fullPath,
        () => {
            const key = route.meta.page || 'home';
            const m = site.meta[key] || site.meta.home;
            if (!m) return;
            document.title = m.title;
            setMeta('meta[name="description"]', 'content', m.description);
            setMeta('meta[property="og:title"]', 'content', m.title);
            setMeta('meta[property="og:description"]', 'content', m.description);
            const rest = route.path.replace(/^\/(en|ar)/, '');
            setMeta('link[rel="canonical"]', 'href', location.origin + route.path);
            setMeta('meta[property="og:url"]', 'content', location.origin + route.path);
            for (const l of site.locales) setMeta(`link[hreflang="${l}"]`, 'href', `${location.origin}/${l}${rest}`);
        },
        { immediate: false },
    );
}

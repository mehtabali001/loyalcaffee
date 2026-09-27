import { createRouter, createWebHistory, RouterView } from 'vue-router';

const page = (name, loader) => ({ path: name === 'home' ? '' : name, name, component: loader, meta: { page: name } });

export function makeRouter(locales) {
    return createRouter({
        history: createWebHistory(),
        routes: [
            {
                path: `/:locale(${locales.join('|')})`,
                component: RouterView,
                children: [
                    page('home', () => import('./pages/HomePage.vue')),
                    page('menu', () => import('./pages/MenuPage.vue')),
                    page('gallery', () => import('./pages/GalleryPage.vue')),
                    page('match-nights', () => import('./pages/MatchesPage.vue')),
                    page('reserve', () => import('./pages/ReservePage.vue')),
                    page('contact', () => import('./pages/ContactPage.vue')),
                    { path: ':rest(.*)*', name: 'not-found', component: () => import('./pages/NotFoundPage.vue'), meta: { page: 'home' } },
                ],
            },
            { path: '/:rest(.*)*', component: () => import('./pages/NotFoundPage.vue') },
        ],
        scrollBehavior(to, from, saved) {
            if (saved) return saved;
            if (to.hash) return { el: to.hash, top: 90 };
            return { top: 0 };
        },
    });
}

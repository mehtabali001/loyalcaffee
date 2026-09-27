import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from './stores/auth';
import { resources } from './resources';

const routes = [
    { path: '/login', component: () => import('./pages/LoginPage.vue'), meta: { public: true, title: 'Sign in' } },
    { path: '/', component: () => import('./pages/DashboardPage.vue'), meta: { title: 'Dashboard' } },
    { path: '/reservations', component: () => import('./pages/ReservationsPage.vue'), meta: { title: 'Reservations' } },
    { path: '/messages', component: () => import('./pages/MessagesPage.vue'), meta: { title: 'Messages' } },
    { path: '/settings', component: () => import('./pages/SettingsPage.vue'), meta: { title: 'Settings' } },
    { path: '/translations', component: () => import('./pages/TranslationsPage.vue'), meta: { title: 'Website text' } },
    { path: '/users', component: () => import('./pages/UsersPage.vue'), meta: { title: 'Staff accounts', owner: true } },
    { path: '/profile', component: () => import('./pages/ProfilePage.vue'), meta: { title: 'My account' } },
    { path: '/r/:resource', component: () => import('./pages/ResourceIndex.vue'), props: true },
    { path: '/r/:resource/:id', component: () => import('./pages/ResourceForm.vue'), props: true },
    { path: '/:rest(.*)*', redirect: '/' },
];

export const router = createRouter({ history: createWebHistory('/admin/'), routes });

router.beforeEach(async (to) => {
    const auth = useAuth();
    await auth.init();
    if (to.params.resource && !resources[to.params.resource]) return '/';
    if (!to.meta.public && !auth.loggedIn) return { path: '/login', query: to.fullPath !== '/' ? { next: to.fullPath } : {} };
    if (to.path === '/login' && auth.loggedIn) return '/';
    if (to.meta.owner && !auth.isOwner) return '/';
});

router.afterEach((to) => {
    const t = to.meta.title || resources[to.params.resource]?.title;
    document.title = t ? `${t} · Loyal Admin` : 'Loyal Admin';
});

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import { router } from './router';
import { setUnauthorizedHandler } from './api';
import { useAuth } from './stores/auth';

const app = createApp(App);
const pinia = createPinia();
app.use(pinia).use(router);

// Expired/revoked token: drop it and bounce to sign-in, remembering where we were
setUnauthorizedHandler(() => {
    useAuth(pinia).logout(false);
    const here = router.currentRoute.value;
    if (!here.meta.public) router.push({ path: '/login', query: { next: here.fullPath } });
});

app.mount('#admin');

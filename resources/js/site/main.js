import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import { makeRouter } from './router';
import { makeI18n } from './i18n';
import { useSite } from './stores/site';
import { reveal, parallax } from './directives/motion';

const boot = window.__LOYAL__ || {};
const app = createApp(App);
const pinia = createPinia();
app.use(pinia);
app.directive('reveal', reveal);
app.directive('parallax', parallax);

const site = useSite();
app.use(makeI18n(site.locale, boot.messages || {}));
app.use(makeRouter(site.locales));

// The server already set <html lang/dir>; this keeps them honest if the shell was cached.
document.documentElement.lang = site.locale;
document.documentElement.dir = site.rtl ? 'rtl' : 'ltr';

app.mount('#app');

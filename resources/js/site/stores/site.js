import { defineStore } from 'pinia';
import { createHttp } from '@/shared/http';
import { useCafeMood } from '../composables/useTime';

const boot = window.__LOYAL__ || {};

export const useSite = defineStore('site', {
    state: () => ({
        locale: boot.locale || 'en',
        locales: boot.locales || ['en', 'ar'],
        rtl: !!boot.rtl,
        settings: boot.settings || {},
        offers: boot.offers || [],
        meta: boot.meta || {},
        // Server-rendered data for the first route — consumed once, then fetched normally
        prefetch: { ...(boot.prefetch || {}) },
        cache: {},
        heroMood: useCafeMood(), // 'day' | 'night' by the cafe's clock; visitors can flip it
    }),
    getters: {
        whatsappLink: (s) => (text) => {
            const n = s.settings.whatsapp;
            if (!n) return null;
            const msg = text ?? s.settings.whatsapp_message ?? '';
            return `https://wa.me/${n}${msg ? `?text=${encodeURIComponent(msg)}` : ''}`;
        },
        telLink: (s) => (s.settings.phone_intl ? `tel:${s.settings.phone_intl}` : null),
    },
    actions: {
        http() {
            return (this._http ||= createHttp(() => this.locale));
        },
        /** Cached GET: prefetch → memory → network. */
        async load(key, url) {
            if (this.prefetch[key]) {
                this.cache[key] = this.prefetch[key];
                delete this.prefetch[key];
            }
            if (this.cache[key]) return this.cache[key];
            const { data } = await this.http().get(url);
            this.cache[key] = data;
            return data;
        },
        /** Build a locale-prefixed internal path; leaves absolute/external links alone. */
        lp(path = '/') {
            if (!path) return `/${this.locale}`;
            if (/^(https?:|mailto:|tel:|#)/.test(path)) return path;
            if (new RegExp(`^/(${this.locales.join('|')})(/|$)`).test(path)) return path;
            return `/${this.locale}${path === '/' ? '' : path.startsWith('/') ? path : `/${path}`}`;
        },
    },
});

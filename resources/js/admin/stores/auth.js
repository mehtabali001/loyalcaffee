import { defineStore } from 'pinia';
import { api, token } from '../api';

export const useAuth = defineStore('auth', {
    state: () => ({ user: null, ready: false }),
    getters: {
        loggedIn: (s) => !!s.user,
        isOwner: (s) => s.user?.role === 'owner',
    },
    actions: {
        async init() {
            if (this.ready) return;
            if (token.get()) {
                try {
                    this.user = (await api.get('/me')).data;
                } catch {
                    token.set(null);
                }
            }
            this.ready = true;
        },
        async login(email, password) {
            const { data } = await api.post('/login', { email, password });
            token.set(data.token);
            this.user = data.user;
        },
        async logout(remote = true) {
            if (remote) await api.post('/logout').catch(() => {});
            token.set(null);
            this.user = null;
        },
    },
});

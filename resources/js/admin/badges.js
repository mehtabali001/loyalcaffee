import { reactive } from 'vue';
import { api } from './api';

/** Sidebar counters, refreshed by the dashboard and after inbox/reservation changes. */
export const badges = reactive({ pending: 0, unread: 0 });

export async function refreshBadges() {
    try {
        const { data } = await api.get('/dashboard');
        badges.pending = data.today.pending;
        badges.unread = data.unread_messages;
        return data;
    } catch {
        return null;
    }
}

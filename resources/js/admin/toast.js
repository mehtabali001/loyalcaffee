import { reactive } from 'vue';

export const toasts = reactive([]);
let id = 0;

export function toast(message, type = 'success', ms = 3500) {
    const t = { id: ++id, message, type };
    toasts.push(t);
    setTimeout(() => {
        const i = toasts.indexOf(t);
        if (i > -1) toasts.splice(i, 1);
    }, ms);
}

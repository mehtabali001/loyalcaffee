import { computed, ref } from 'vue';
import { api } from './api';

/** Shared, lazily loaded option lists for `remote` selects (e.g. menu categories). */
const cache = {};

export function useOptions(source) {
    if (!cache[source]) {
        cache[source] = ref([]);
        api.get(`/${source}`).then(({ data }) => (cache[source].value = data.data || data)).catch(() => {});
    }
    return {
        options: computed(() => cache[source].value.map((r) => ({ value: r.id, label: r.name?.en || r.title?.en || `#${r.id}` }))),
    };
}

export function invalidateOptions(source) {
    delete cache[source];
}

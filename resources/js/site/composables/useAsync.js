import { ref } from 'vue';

/** Tiny loader: { data, error, loading, run }. Keeps pages free of try/catch noise. */
export function useAsync(fn) {
    const data = ref(null);
    const error = ref(null);
    const loading = ref(true);
    const run = async () => {
        loading.value = true;
        error.value = null;
        try {
            data.value = await fn();
        } catch (e) {
            error.value = e;
        } finally {
            loading.value = false;
        }
    };
    run();
    return { data, error, loading, run };
}
